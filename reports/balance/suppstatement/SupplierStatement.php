<?php

	$PathPrefix='../../../';

	include('../../../quotation/misc.php');
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	$db = createDBConnection();

	// $debtorno = $_GET['debtorno'];

	$customerStatement = [];

	$debtorno	= $_POST['cust'];
	$branchno	= $_POST['cust'];
	$fromdate 	= $_POST['fromdate'];
	$todate 	= $_POST['todate'];

	if($todate == "")
		$todate = date("Y-m-d");

	$SQL = "SELECT * FROM suppliers 
			WHERE supplierid='".$debtorno."'";

	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) < 1){

		echo "Incorrect debtorno or branchno";
		exit;

	} 

	$debtorsMaster = mysqli_fetch_assoc($res);

	$customerStatement['name'] 		= $debtorsMaster['suppname'];
	$customerStatement['address1'] 	= $debtorsMaster['address1'];
	$customerStatement['address2'] 	= $debtorsMaster['address2'];
	$customerStatement['address3'] 	= $debtorsMaster['address3'];
	$customerStatement['address4'] 	= $debtorsMaster['address4'];
	$customerStatement['address5'] 	= $debtorsMaster['address5'];
	$customerStatement['address6'] 	= $debtorsMaster['address6'];

	//------Opening Balance

	$SQL = 'SELECT SUM(ovamount) as openingbalance
			FROM supptrans
	  		WHERE supplierno="'.$debtorno.'" 
	  		AND trandate < "'.($fromdate).'"'; //FormatDateForSQL
	
	$res = mysqli_query($db, $SQL);

	$customerStatement['openingbalance'] = mysqli_fetch_assoc($res)['openingbalance'] ?: 0;

	$SQL = 'SELECT * FROM supptrans
			WHERE supplierno="'.$debtorno.'"
			AND trandate >= "'.($fromdate).'"
			AND trandate <= "'.($todate)." 23:59:59".'"
			ORDER BY trandate';

	$res = mysqli_query($db, $SQL);

	$customerStatement['statement'] = [];

	while( $row = mysqli_fetch_assoc($res) ){

        if($row['type'] == 22){

            $SQL = "SELECT supptrans.transno
                    FROM suppallocs
                    INNER JOIN supptrans ON supptrans.id = suppallocs.transid_allocto
                    INNER JOIN gltrans ON gltrans.typeno=supptrans.transno
                    WHERE suppallocs.transid_allocfrom = '".$row['id'].
                "' GROUP BY transno";

            $row['billNo'] = "(";
            $result = mysqli_query($db, $SQL);
            while($r = mysqli_fetch_assoc($result)){
                $row['billNo'] .= $r['transno'] . ", ";
            }
            $row['billNo'] .= ")";


        }

		$customerStatement['statement'][] = $row;

	}

?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Statement</title>
    <!-- <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->
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
        padding-top: 0px;
        padding-bottom: 0px;
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
        font-size: 12px;
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
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
        	<tr>
        		<td colspan="2" 
        			style="font-size: 12px; text-align: center; border: 1px solid #424242;border-bottom: 1px solid #eee;">
        			Vendor Statement : <?php echo $customerStatement['name']; ?>
        		</td>
        	</tr>
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td style="padding-bottom: 0px">
                                <span style="font-size: 35px;">
       	 							<img src="/sahamid/companies/sahamiduat/EDI_Incoming_Orders/companylogos/SAH.jpg"
       	 								 height="90px" width="90px" alt="">
                                </span>
                            </td>
                            
                            <td style="font-size: 12px">
                                Date Printed: <?php echo date('d/m/Y'); ?><br>
                                From Date: <?php echo (($fromdate != "") ? date('d/m/Y', strtotime($fromdate)) : "Begining"); ?><br>
                                To Date: <?php echo (($todate != "") ? date('d/m/Y', strtotime($todate)) : date('d/m/Y')) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information" style="display: none;">
                <td colspan="2">
                    <table>
                        <tr>
                        	<td style="font-size: 12px; display: none;">
                                <?php echo $customerStatement['dba']; ?><br>
                                <i class="fa fa-envelope" aria-hidden="true"></i> Email,
                                <i class="fa fa-fax" aria-hidden="true"></i> Fax, 
                                <i class="fa fa-phone" aria-hidden="true"></i> No <br>
                                NTN: -----------

                            </td>

                            <td style="font-size: 12px; display: none;">
                            	<?php echo $customerStatement['name']; ?><br>
                                <?php echo $customerStatement['address1']; ?><br>
                                <?php echo $customerStatement['address2'].", ".
                                			$customerStatement['address4'].", ".
                                			$customerStatement['address5'].", ".
                                			$customerStatement['address6']; ?><br>
                               
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
        		<td colspan="2" style="text-align: left !important; font-weight: bold; font-size: 12px">Opening Balance: </td>
        		<td style="text-align: right !important; font-weight: bold; font-size: 12px"><?php echo round($customerStatement['openingbalance'],2); ?> <span style="font-size: 9px">PKR</span></td>
        	</tr>
            <tr class="heading" style="text-align: left;">
                <td class="nostretch" style="font-size: 12px;">
                    Date
                </td>
                <td class="nostretch" style="font-size: 12px;">
                	Market Slip #
                </td>
                <td class="nostretch" style="font-size: 12px;">
                    Payment
                </td>
                <td class="nostretch" style="font-size: 12px;">
                    Cheque#
                </td>
                <td class="nostretch" style="font-size: 12px; text-align: center;">
                    Status
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
            </tr>
            <?php $ob = $customerStatement['openingbalance']; ?>
            <?php

            	$totalDebit 	= 0;
            	$totalCredit 	= 0;

            	foreach ($customerStatement['statement'] as $statement) { 
            		$ob += $statement['ovamount']; 
            ?>
            <tr class="item">
        		<td class="nostretch" style="font-size: 12px; width: 1%">
					<?php echo date('d/m/Y',strtotime(explode(' ',$statement['trandate'])[0])); ?>
				</td>
            	<?php 
            		if($statement['type'] == 22){

            			$totalDebit += abs($statement['ovamount']);
						
						
            	?>
					
            		<td class="nostretch" style="font-size: 10px">
                        <?php echo $statement['billNo']; ?>
                    </td>
            		<td class="nostretch" style="font-size: 12px">
	                    <?php echo $statement['suppreference']; ?>
	              	</td>
                        <td class="nostretch" style="font-size: 12px">
                            <?php echo $statement['transtext']; ?>
                        </td>
	              	<td class="nostretch" style="font-size: 12px">
	              		<span style="font-size: 12px">

	              			<?php echo ($statement['settled'] == 1) ? "Alloc":"NY-Alloc"; ?>
	              		</span>
	              	</td>
	              	<td class="nostretch" style="font-size: 12px">
	                    <?php echo locale_number_format(abs(round($statement['ovamount'],2))); ?>
	              	</td>
	              	<td></td>
            	<?php 
            		} else if($statement['type'] == 601 and $statement['reversed'] == 0){
            			$totalCredit += abs($statement['ovamount']);
            	?>
	              	<td class="nostretch" style="font-size: 12px">
	                    MPIW-<?php echo $statement['transno']; ?><br>
	                    <!-- <span style="font-size: 10px">MPIW-<?php echo $statement['transno']; ?></span> -->
	              	</td>
	              	<td></td>
                        <td></td>
	              	<td class="nostretch" style="font-size: 12px"><span style="font-size: 12px"><?php echo ($statement['settled'] == 1) ? "Paid":"Not Paid (".locale_number_format($statement['ovamount']-$statement['alloc']).")"; ?></span></td>
	              	<td></td>
	              	<td class="nostretch" style="font-size: 12px">
	                    <?php echo locale_number_format(abs(round($statement['ovamount'],2))); ?>
	              	</td>
            	<?php }else if($statement['type'] == 601 and $statement['reversed'] == 1){
                        $totalCredit += abs($statement['ovamount']);
                        ?>
                        <td class="nostretch" style="font-size: 12px">
                            MPIW-<?php echo $statement['transno']; ?><br>
                            <!-- <span style="font-size: 10px">MPIW-<?php echo $statement['transno']; ?></span> -->
                        </td>
                        <td></td>
                        <td></td>
                        <td class="nostretch" style="font-size: 12px"><span style="font-size: 12px"><?php echo "Returned By ".$statement['updated_by']; ?></span></td>
                        <td></td>
                        <td class="nostretch" style="font-size: 12px">
                            <?php echo locale_number_format(abs(round($statement['ovamount'],2))); ?>
                        </td>
                    <?php }
            		else if($statement['type'] == 14){
                        $totalCredit += abs($statement['ovamount']);
                        ?>
                        <td class="nostretch" style="font-size: 12px">
                            MPIW-<?php echo $statement['transno']; ?><br>
                            <!-- <span style="font-size: 10px">MPIW-<?php echo $statement['transno']; ?></span> -->
                        </td>
                        <td></td>
                        <td></td>
                        <td class="nostretch" style="font-size: 12px"><span style="font-size: 12px"><?php echo "Returned By ".$statement['updated_by']; ?></span></td>

                        <td class="nostretch" style="font-size: 12px">
                            <?php echo locale_number_format(abs(round($statement['ovamount'],2))); ?>
                        </td>
                        <td></td>
                    <?php } ?>
            	<td class="nostretch" style="text-align: right !important; font-size: 12px">
                    <?php echo locale_number_format((-1)*(round($ob,2))); ?>
              	</td>
            </tr>
            <?php } ?>
            <tr class="heading">
            	<td style="background: white !important; border: 0px"></td>
            	<td style="background: white !important; border: 0px"></td>
            	<td style="background: white !important; border: 0px"></td>
            	<td class="nostretch" style="text-align: left !important; font-size: 12px;">Total: </td>
            	<td class="nostretch" style="text-align: left !important; font-size: 12px;"><?php echo locale_number_format($totalDebit); ?></td>
            	<td class="nostretch" style="text-align: left !important; font-size: 12px;"><?php echo locale_number_format($totalCredit); ?></td>
            	<td class="nostretch" style="text-align: right !important; font-size: 12px;"><?php echo locale_number_format((-1)*$ob); ?></td>
            </tr>
            <tr>
            	<td class="nostretch" colspan="6" style="font-size: 12px; font-weight: bold;">Note:</td>
            </tr>
            <tr class="item">
            	<td class="nostretch" colspan="7" style="font-size: 12px; ">All prices mentioned are in PKR</td>
            </tr>
            <tr>
            	<td colspan="7" 
            		style="text-align: center; font-size: 12px; border: 1px solid #424242; border-top: 0;">
            		Powered By:
        			<img src="../logo.png" height="15px" width="15px" alt="">
            		Compresol
            	</td>
            </tr>
        </table>
    </div>
</body>
</html>