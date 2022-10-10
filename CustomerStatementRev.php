<?php

	include('quotation/misc.php');
	include('includes/session.inc');
	include('includes/SQL_CommonFunctions.inc');

	$db = createDBConnection();

	// $debtorno = $_GET['debtorno'];

	$customerStatement = [];
	
	$debtorno	= $_POST['cust'];
	$branchno	= $_POST['cust'];
	$fromdate 	= $_POST['fromdate'];

	$SQL = "SELECT * FROM debtorsmaster 
			INNER JOIN custbranch ON debtorsmaster.debtorno = custbranch.debtorno
			WHERE custbranch.debtorno='".$debtorno."'";

	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) < 1){

		echo "Incorrect debtorno or branchno";
		exit;

	} 

	$debtorsMaster = mysqli_fetch_assoc($res);

	$customerStatement['dba'] 			= $debtorsMaster['dba'];
	$customerStatement['name'] 			= $debtorsMaster['name'];
	$customerStatement['debtorno'] 		= $debtorsMaster['debtorno'];
	$customerStatement['branchcode'] 	= $debtorsMaster['branchcode'];
	$customerStatement['braddress1'] 	= $debtorsMaster['braddress1'];
	$customerStatement['braddress2'] 	= $debtorsMaster['braddress2'];
	$customerStatement['braddress3'] 	= $debtorsMaster['braddress3'];
	$customerStatement['braddress4'] 	= $debtorsMaster['braddress4'];
	$customerStatement['braddress5'] 	= $debtorsMaster['braddress5'];
	$customerStatement['braddress6'] 	= $debtorsMaster['braddress6'];

	//------Opening Balance

	$SQL = 'SELECT SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
								THEN ovamount
							WHEN GSTwithhold = 0 AND WHT = 1 
								THEN ovamount + WHTamt
							WHEN GSTwithhold = 1 AND WHT = 0 
								THEN ovamount + GSTamt
							WHEN GSTwithhold = 1 AND WHT = 1 
								THEN ovamount + GSTamt + WHTamt
						END
					) as openingbalance
			FROM debtortrans
	  		WHERE debtorno="'.$debtorno.'" 
	  		AND trandate < "'.($fromdate).'"'; //FormatDateForSQL
	
	$res = mysqli_query($db, $SQL);

	$customerStatement['openingbalance'] = mysqli_fetch_assoc($res)['openingbalance'] ?: 0;

	//---------------------

	//------30 Days Balance

	$SQL = 'SELECT SUM(
					CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount - alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS due 
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno 
			WHERE debtortrans.debtorno="'.$debtorno.'" 
			AND debtortrans.type=10 
			AND debtortrans.settled=0 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 30 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 60'; //FormatDateForSQL
	
	$res = mysqli_query($db, $SQL);

	$customerStatement['30daysdue'] = mysqli_fetch_assoc($res)['due'] ?: 0;

	//---------------------

	//------60 Days Balance

	$SQL = 'SELECT SUM(
					CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount - alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS due 
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno 
			WHERE debtortrans.debtorno="'.$debtorno.'" 
			AND debtortrans.type=10 
			AND debtortrans.settled=0 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 60 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 90'; //FormatDateForSQL
	
	$res = mysqli_query($db, $SQL);

	$customerStatement['60daysdue'] = mysqli_fetch_assoc($res)['due'] ?: 0;

	//---------------------

	//------90 Days Balance

	$SQL = 'SELECT SUM(
					CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount - alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS due 
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno 
			WHERE debtortrans.debtorno="'.$debtorno.'" 
			AND debtortrans.type=10 
			AND debtortrans.settled=0 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 90 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 120'; //FormatDateForSQL
	
	$res = mysqli_query($db, $SQL);

	$customerStatement['90daysdue'] = mysqli_fetch_assoc($res)['due'] ?: 0;

	//---------------------

	//------120 Days Balance

	$SQL = 'SELECT SUM(
					CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount - alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS due 
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno 
			WHERE debtortrans.debtorno="'.$debtorno.'" 
			AND debtortrans.type=10 
			AND debtortrans.settled=0 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 120'; //FormatDateForSQL
	
	$res = mysqli_query($db, $SQL);

	$customerStatement['120daysdue'] = mysqli_fetch_assoc($res)['due'] ?: 0;

	//----------------------

	$SQL = 'SELECT debtortrans.id,
					debtortrans.debtorno,
					debtortrans.branchcode, 
					debtortrans.transno, 
					debtortrans.type, 
					debtortrans.trandate, 
					debtortrans.reference, 
					debtortrans.order_, 
					debtortrans.ovamount, 
					debtortrans.alloc,
					debtortrans.WHT, 
					debtortrans.GSTwithhold, 
					debtortrans.WHTamt, 
					debtortrans.GSTamt, 
					debtortrans.GSTtotalamt, 
					debtortrans.settled, 
					debtortrans.invtext as chequeno,
					debtortrans.processed,
					debtortrans.reversed,
					debtortrans.reverseHistory,					
					dcgroups.dcnos,
					shopsale.payment,
					invoice.invoiceno,
					invoice.invoicedate,
					invoice.customerref,
					invoice.podate,
					invoice.invoicesdate,
					invoice.due,
					invoice.shopinvoiceno
			FROM debtortrans 
			LEFT OUTER JOIN invoice ON ( invoice.invoiceno = debtortrans.transno AND debtortrans.type=10 )
			LEFT OUTER JOIN shopsale ON ( shopsale.orderno = debtortrans.transno AND debtortrans.type=750 )
			LEFT OUTER JOIN bazar_parchi ON ( bazar_parchi.transno = debtortrans.transno 
				AND debtortrans.type=602 
				AND bazar_parchi.type=602 )
			LEFT OUTER JOIN dcgroups ON ( invoice.groupid = dcgroups.id )
			WHERE debtortrans.debtorno="'.$debtorno.'"
			AND trandate >= "'.($fromdate).'" 
			ORDER BY debtortrans.id';

	$res = mysqli_query($db, $SQL);

	$customerStatement['statement'] = [];

	while( $row = mysqli_fetch_assoc($res) ){

		$ageing = "";
		
		if($row['type'] == 12 || $row['type'] == 13 || $row['type'] == 750 || $row['type'] == 602)
			$row['orderby'] = $row['trandate'];
		else{
			$row['orderby'] = $row['invoicesdate'] != "" ? $row['invoicesdate'] : $row['invoicedate'];
		}
		
		$row['orderby'] = strtotime($row['orderby']);

		if($row['processed'] != 0){

			$now = time();
			$due = strtotime((($row['type'] == 750 || $row['type'] == 602) ? $row['trandate']:$row['invoicesdate']));
			$day = floor( ($now-$due) / (60 * 60 * 24));


			if($row['processed'] == -1){
				$ageing = '<span style="font-size:12px">Due</span>';
				if($day > 0)
					$ageing .= '<br><span style="font-size:12px">('.$day.'D)</span>'; 
			}else if($row['settled'] == 0){
				$ageing = '<span style="font-size:12px">P-Paid</span>';
				if($day > 0)
					$ageing .= '<br><span style="font-size:12px">('.$day.'D)</span>'; 
			}else{
				$ageing = '<span style="font-size:12px">Paid</span>';
			}

			if($row['settled'] == 1){
				$ageing = '<span style="font-size:12px; color:green">Paid</span>';
			}
			
			if($row['reversed'] == 1){
				$ageing = '<span style="font-size:12px; color:blue">Returned</span>';
			}

		} else {

			$SQL = "SELECT SUM(WHTamt) as WHTamt FROM debtortrans WHERE processed='".$row['id']."' AND WHT=1";
			$r 	 = mysqli_query($db, $SQL);
			$row['WHTamtc']  = round((mysqli_fetch_assoc($r)['WHTamt'] ?:0),2);

			$SQL = "SELECT SUM(GSTamt) as GSTamt FROM debtortrans WHERE processed='".$row['id']."' AND GSTwithhold=1";
			$r 	 = mysqli_query($db, $SQL);
			$row['GSTamtc']  = round((mysqli_fetch_assoc($r)['GSTamt'] ?:0),2);

		}

		$row['ageing'] = $ageing;

		if($row['invoicesdate'] == '0000-00-00' || $row['invoicesdate'] == ''){
			$row['invoicesdate'] = "";
		}else{
			$row['invoicesdate'] = "(".date('d/m/Y',strtotime($row['invoicesdate'])).")";
		}
		
		if($row['trandate'] == '0000-00-00'){
			$row['trandate'] = "";
		}else{
			$row['trandate'] = "(".date('d/m/Y',strtotime($row['trandate'])).")";
		}

		if($row['podate'] == '0000-00-00' || $row['podate'] == ''){
			$row['podate'] = "";
		}else{
			$row['podate'] = "(".date('d/m/Y',strtotime($row['podate'])).")";
		}

		$row['dcnos'] = implode("", explode(",", $row['dcnos'], 2));

		if(!isset($customerStatement['unallocated']))
			$customerStatement['unallocated'] = 0;

		$row['billNo'] = "(";
		if($row['type'] == 12){
			$customerStatement['unallocated'] += abs($row['ovamount'] - $row['alloc']); 
			$SQL = "SELECT invoice.shopinvoiceno,debtortrans.type,debtortrans.transno 
					FROM custallocns 
					INNER JOIN debtortrans ON custallocns.transid_allocto = debtortrans.id
					INNER JOIN invoice ON invoice.invoiceno = debtortrans.transno
					WHERE custallocns.transid_allocfrom = '".$row['id']."'";
			$reSsS = mysqli_query($db, $SQL);
			while($resRow = mysqli_fetch_array($reSsS)){
				if($resRow['type'] == 10)
					$row['billNo'] .= $resRow['shopinvoiceno'] . ", ";
				else
					$row['billNo'] .= $resRow['transno'] . ", ";
			}
			$row['billNo'] .= ")";
		}

		$customerStatement['statement'][] = $row;

	}
	
	usort($customerStatement['statement'], "cmp");
	
	function cmp($a, $b) {
		if ($a['orderby'] == $b['orderby'])	return 0;
		return ($a['orderby'] > $b['orderby']) ? 1 : -1;
	}

?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Statement</title>
    <link rel="stylesheet" type="text/css" href="quotation/assets/vendor/font-awesome/css/font-awesome.css">
    <style>

    .invoice-box {
        max-width: 100%;
        margin: auto;
        padding: 30px;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        font-size: 16px;
        line-height: 24px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #555;
    }
    
    .invoice-box table {
        width: 100%;
        line-height: inherit;
        text-align: left;
    }
    
    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }
    
    .invoice-box table tr td:nth-child(2) {
        text-align: right;
    }
    
    .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }
    
    .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
    }
    
    .invoice-box table tr.information table td {
        padding-bottom: 10px;
    }
    
    .invoice-box table tr.heading td {
        background: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }
    
    .invoice-box table tr.details td {
        padding-bottom: 20px;
    }
    
    .invoice-box table tr.item td{
        border-bottom: 1px solid #eee;
    }
    
    .invoice-box table tr.item.last td {
        border-bottom: none;
    }
    
    .invoice-box table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
    }
    .invoice-box .heading td {
    	text-align: left !important;
    }
    
    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td {
            width: 100%;
            display: block;
            text-align: center;
        }
        
        .invoice-box table tr.information table td {
            width: 100%;
            display: block;
            text-align: center;
        }
    }

    .item td{
    	text-align: left !important;
    }
    
    /** RTL **/
    .rtl {
        direction: rtl;
        font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }
    
    .rtl table {
        text-align: right;
    }
    
    .rtl table tr td:nth-child(2) {
        text-align: left;
    }

    .nostretch{
    	width:1%;
    	white-space:normal;
    	line-height: normal;
    }
    .footer{
    	width: 100%; 
    	font-size: 14px; 
    	padding-left: 10px;
    	border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        font-size: 13px;
    }
    </style>
    <link rel="stylesheet" href="quotation/assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="quotation/assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="quotation/assets/vendor/nanoscroller/nanoscroller.css" />
    <link rel="stylesheet" href="quotation/assets/vendor/sweetalert/sweetalert.css" />
    <link rel="stylesheet" href="quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
    <link rel="stylesheet" href="quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
    <link rel="stylesheet" href="quotation/assets/vendor/pnotify/pnotify.custom.css">
    <link rel="stylesheet" href="quotation/assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="quotation/assets/stylesheets/skins/default.css" />
</head>

<body>
<script src="quotation/assets/vendor/jquery/jquery.js"></script>
<script src="quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
<script src="quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
<script src="quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
<script src="quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
<script src="quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
<script src="quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
<script src="quotation/assets/vendor/pnotify/pnotify.custom.js"></script>



<script>

        $(document.body).on("click",".reverse",function(){

            let FormID = '<?php echo $_SESSION['FormID']; ?>';

            let transno=$(this).val();

            swal({
                    title: "Are you sure?",
                    text: `Are you sure you want to reverse this receipt!
                    (To cancel press continue without giving reason)`,
                    type: "input",
                    showCancelButton: false,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Continue!",
                    cancelButtonText: "Cancel!",
                    closeOnConfirm: false,
                    closeOnCancel: true,
                    inputPlaceholder: "Reason"
                },
                function(inputVal){
                    if(inputVal.trim() == ""){
                        swal.close();
                        return;
                    }
                    let reason = inputVal.trim();


                    $.get("corrections/chequeCorrection.php",{FormID, transno,reason},function(res, status, something){

                        res = JSON.parse(res);
                        console.log(res);
                        if(res.status == "success"){
                            window.location.reload();
                        }else{
                            swal("Error",res.message,"error");
                        }

                    });

                });

        });

</script>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="/sahamidtest/companies/sahamidtest/EDI_Incoming_Orders/companylogos/<?php echo $customerStatement['dba']; ?>.jpg" style="width:auto; max-width:auto; height: 50px">
                            </td>
                            
                            <td style="font-size: 12px">
                                <!-- Date Printed: <?php echo date('d/m/Y'); ?><br> -->
                                From Date: <?php echo (($fromdate != "") ? date('d/m/Y', strtotime($fromdate)) : "Begining"); ?><br>
                                To Date: <?php echo date('d/m/Y'); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                        	<td style="font-size: 12px">
                                <?php echo $customerStatement['dba']; ?><br>
                                <i class="fa fa-envelope" aria-hidden="true"></i> Email,
                                <i class="fa fa-fax" aria-hidden="true"></i> Fax, 
                                <i class="fa fa-phone" aria-hidden="true"></i> No <br>
                                NTN: -----------

                            </td>

                            <td style="font-size: 12px">
                            	<?php echo $customerStatement['name']; ?><br>
                                <?php echo $customerStatement['braddress1']; ?><br>
                                <?php echo $customerStatement['braddress2'].", ".
                                			$customerStatement['braddress4'].", ".
                                			$customerStatement['braddress5'].", ".
                                			$customerStatement['braddress6']; ?><br>
                               
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0">
        	<tr>
        		<td></td>
        		<td></td>
        		<td></td>
        		<td></td>
        		<td></td>
        		<td></td>
        		<td colspan="2" style="text-align: left !important; font-weight: bold; font-size: 12px">Opening Balance: </td>
        		<td style="text-align: right !important; font-weight: bold; font-size: 12px"><?php echo locale_number_format(round($customerStatement['openingbalance'],2),2); ?> <span style="font-size: 9px">PKR</span></td>
        	</tr>
            <tr class="heading" style="text-align: left;">
                <td class="nostretch" style="font-size: 12px;">
                    Date
                </td>
                <td class="nostretch" style="font-size: 12px;">
                    PO #
                </td>
                <td class="nostretch" style="font-size: 12px;">
                	Invoice #
                </td>
                <td class="nostretch" style="font-size: 12px;">
                	DC #
                </td>
                <td class="nostretch" style="font-size: 12px;">
                    Cheque #
                </td>
                <td class="nostretch" style="text-align: center !important; font-size: 12px">
                    Ageing
                </td>
                <td class="nostretch" style="font-size: 12px;">
                    Debit
                </td>
                <td class="nostretch" style="font-size: 12px;">
                    Credit
                </td>
                <td class="nostretch" style="text-align: right !important; font-size: 12px">
                    Balance
                </td>
                <td class="nostretch" style="text-align: right !important; font-size: 12px">
                    Reverse
                </td>
                <td class="nostretch" style="text-align: right !important; font-size: 12px">
                    Reverse History
                </td>
            </tr>
            <?php $ob = $customerStatement['openingbalance']; ?>
            <?php

            	$totalDebit 	= 0;
            	$totalCredit 	= 0;

            	foreach ($customerStatement['statement'] as $statement) { 
            		$ob += $statement['ovamount']; 
            ?>
            <tr class="item">
            	<?php 
            		if($statement['type'] == 12){ 
            			
            			$ob -= $statement['WHTamtc'];
            			$ob -= $statement['GSTamtc'];

            			$bl = $statement['ovamount']-$statement['WHTamtc']-$statement['GSTamtc'];
						
						$totalCredit += abs($bl);
						
            	?>
					<td class="nostretch" style="font-size: 12px">
						<?php echo $statement['trandate']; ?>
					</td>
            		<td class="nostretch" colspan="3" style="font-size: 10px">
            			<?php echo "WHT: ".$statement['WHTamtc'].", GSTwithhold: ".$statement['GSTamtc']; ?><br>
						<?php echo $statement['billNo']; ?>
            			<!--(Billno1,Billno2,Billno3,Billno4,Billno5)-->
            		</td>
            		<td class="nostretch" style="font-size: 12px">
	                    <?php echo $statement['chequeno']; ?>
	              	</td>
	              	<td class="nostretch" style="font-size: 12px; text-align: center !important;">
	              		<?php echo $statement['ageing']; ?>
	              	</td>
	              	<td></td>
	              	<td class="nostretch" style="font-size: 12px">
	                    <?php echo locale_number_format(abs(round($bl,2)),2); ?>
	              	</td>
	              	<td class="nostretch" style="font-size: 12px; text-align: right !important;">
	                    <?php echo locale_number_format(round($ob,2),2); ?>
	              	</td>
                        <td class="nostretch" style="text-align: right !important; font-size: 12px">
                            <?php echo '<button value="'.$statement['transno'].'" class="btn btn-danger reverse"><i class="fa fa-times"></i></button>'; ?>
                        </td>

            	<?php 
            		} else { 
					
						if($statement['type'] == 13){
							$totalCredit += abs($statement['ovamount']);
						}else{
							$totalDebit += abs($statement['ovamount']);
						}
						
            	?>
					<td class="nostretch" style="font-size: 12px">
						<?php 
							echo (($statement['type'] == 13 || $statement['type'] == 750 || $statement['type'] == 602) ? $statement['trandate'] : $statement['invoicesdate']); ?>
					</td>
            		<td class="nostretch" style="font-size: 12px">
	                    <?php echo (($statement['type'] == 750 || $statement['type'] == 602) ? strtoupper($statement['payment']):$statement['customerref']) ?><br>
	                    <span style="font-size: 10px; text-align: center; width: 100%;"><?php echo $statement['podate']; ?></span>
	              	</td>
	              	<td class="nostretch" style="font-size: 12px">
						<?php if($statement['type'] == 13){ ?>
							Returned
						<?php }else{ ?>
							<?php echo (($statement['type'] == 750 || $statement['type'] == 602) ? strtoupper($statement['transno']):$statement['shopinvoiceno']) ?><br>
							<span style="font-size: 10px"><?php echo $statement['invoicesdate']; ?></span>
						<?php } ?>
	              	</td>
	              	<td class="nostretch" style="font-size: 12px">
	              		<?php echo str_replace(",", "<br>", $statement['dcnos']); ?>
	              	</td>
	              	<td class="nostretch" style="font-size: 12px">
						<?php if($statement['type'] == 13){ ?>
							Ret: <?php echo $statement['shopinvoiceno']; ?><br>
							<span style="font-size: 10px"><?php echo $statement['invoicesdate']; ?></span>
						<?php } ?>
					</td>
	              	<td class="nostretch" style="text-align: center !important; font-size: 12px">
	              		<?php if($statement['type'] == 10 || $statement['type'] == 750 || $statement['type'] == 602) echo $statement['ageing']; ?>
	              	</td>
	              	<td class="nostretch" style="font-size: 12px">
	                    <?php if($statement['type'] == 10 || $statement['type'] == 750|| $statement['type'] == 602) echo locale_number_format(abs(round($statement['ovamount'],2)),2); ?>
	              	</td>
	              	<td style="font-size: 12px">
						<?php echo (($statement['type'] == 13 ) ? locale_number_format(abs(round($statement['ovamount'],2)),2) : '') ?>
					</td>
	              	<td class="nostretch" style="text-align: right !important; font-size: 12px">
	                    <?php echo locale_number_format(round($ob,2),2); ?>
	              	</td>
                    <td></td>

                        <td colspan="2" class="nostretch" style="font-size: 12px">
                            <?php echo $statement['reverseHistory']; ?>
                        </td>

            	<?php } ?>
            </tr>
            <?php } ?>
            <tr class="heading">
            	<td style="background: white !important; border: 0px"></td>
            	<td style="background: white !important; border: 0px"></td>

            	<td style="background: white !important; border: 0px"></td>
            	<td style="background: white !important; border: 0px"></td>
            	<td style="background: white !important; border: 0px"></td>
            	<td class="nostretch" style="text-align: center !important; font-size: 12px;">Total: </td>
            	<td class="nostretch" style="text-align: left !important; font-size: 12px;"><?php echo locale_number_format(round($totalDebit,2),2); ?></td>
            	<td class="nostretch" style="text-align: left !important; font-size: 12px;"><?php echo locale_number_format(round($totalCredit,2),2); ?></td>
            	<td class="nostretch" style="text-align: right !important; font-size: 12px;"><?php echo locale_number_format(round($ob,2),2); ?></td>
            </tr>
            <tr><td class="nostretch" style="font-size: 12px; font-weight: bold;">Note:</td></tr>
            <tr class="item">
            	<td class="nostretch" style="font-size: 12px; " colspan="4">All prices mentioned are in PKR</td>
            </tr>
            <tr>
            	<td colspan="9" class="footer" style="font-size: 12px">
            		<!-- Current Balance: <?php echo round($ob,2); ?>, &nbsp; -->
            		Balance Due (
					Total: <?php echo locale_number_format(round($ob,2),2); ?>, &nbsp;
            		30Days: <?php echo locale_number_format(round($customerStatement['30daysdue'],2),2); ?>, &nbsp;
            		60Days: <?php echo locale_number_format(round($customerStatement['60daysdue'],2),2); ?>, &nbsp;
            		90Days: <?php echo locale_number_format(round($customerStatement['90daysdue'],2),2); ?>, &nbsp;
            		Over 120 Days: <?php echo locale_number_format(round($customerStatement['120daysdue'],2),2); ?> )
				</td>
            </tr>
        </table>
    </div>
</body>
</html>

