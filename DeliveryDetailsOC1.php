<?php

/* $Id: DeliveryDetails.php 6457 2013-11-29 14:20:40Z turbopt $ */

/*
This is where the delivery details are confirmed/entered/modified and the order committed to the database once the place order/modify order button is hit.
*/

include('includes/DefineOCCartClass.php');

/* Session started in header.inc for password checking the session will contain the details of the order from the Cart class object. The details of the order come from SelectOrderItemsOC.php 			*/

include('includes/session.inc');
$Title = _('Order Delivery Details');
include('includes/header.inc');
include('includes/FreightCalculation.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');
//print_r($_SESSION['Items'.$identifier]);
//echo $_SESSION['part_pics_dir'] . '/companylogos/' .'HES*';


$img = glob($_SESSION['part_pics_dir'] . '/companylogos/' .'HES*');
//$pdf->addJpegFromFile($img[0], 20, 750, 0, 60);
//print_r($img[0]);

if (isset($_GET['identifier'])) {
	$identifier=$_GET['identifier'];
}

unset($_SESSION['WarnOnce']);
if (!isset($_SESSION['Items'.$identifier]) OR !isset($_SESSION['Items'.$identifier]->DebtorNo)){
	prnMsg(_('This page can only be read if an order has been entered') . '. ' . _('To enter an order select customer transactions then sales order entry'),'error');
	include('includes/footer.inc');
	exit;
}

if ($_SESSION['Items'.$identifier]->ItemsOrdered == 0){
	prnMsg(_('This page can only be read if an there are items on the order') . '. ' . _('To enter an order select customer transactions then sales order entry'),'error');
	include('includes/footer.inc');
	exit;
}

/*Calculate the earliest dispacth date in DateFunctions.inc */

$EarliestDispatch = CalcEarliestDispatchDate();

if (isset($_POST['ProcessOrder']) OR isset($_POST['MakeRecurringOrder'])) {

	/*need to check for input errors in any case before order processed */
	$_POST['Update']='Yes rerun the validation checks'; //no need for gettext!

	/*store the old freight cost before it is recalculated to ensure that there has been no change - test for change after freight recalculated and get user to re-confirm if changed */

	$OldFreightCost = round($_POST['FreightCost'],2);

}

if (isset($_POST['Update'])
	OR isset($_POST['BackToLineDetails'])
	OR isset($_POST['MakeRecurringOrder']))   {

	$InputErrors =0;
	if (mb_strlen($_POST['DeliverTo'])<=1){
		$InputErrors =1;
		prnMsg(_('You must enter the person or company to whom delivery should be made'),'error');
	}
	if (mb_strlen($_POST['BrAdd1'])<=1){
		$InputErrors =1;
		prnMsg(_('You should enter the street address in the box provided') . '. ' . _('Orders cannot be accepted without a valid street address'),'error');
	}
//	if (mb_strpos($_POST['BrAdd1'],_('Box'))>0){
//		prnMsg(_('You have entered the word') . ' "' . _('Box') . '" ' . _('in the street address') . '. ' . _('Items cannot be delivered to') . ' ' ._('box') . ' ' . _('addresses'),'warn');
//	}
	if (!is_numeric($_POST['FreightCost'])){
		$InputErrors =1;
		prnMsg( _('The freight cost entered is expected to be numeric'),'error');
	}
	if (isset($_POST['MakeRecurringOrder']) AND $_POST['Quotation']==1){
		$InputErrors =1;
		prnMsg( _('A recurring order cannot be made from a quotation'),'error');
	}
	if (($_POST['DeliverBlind'])<=0){
		$InputErrors =1;
		prnMsg(_('You must select the type of packlist to print'),'error');
	}

/*	If (mb_strlen($_POST['BrAdd3'])==0 OR !isset($_POST['BrAdd3'])){
		$InputErrors =1;
		echo "<br />A region or city must be entered.<br />";
	}

	Maybe appropriate in some installations but not here
	If (mb_strlen($_POST['BrAdd2'])<=1){
		$InputErrors =1;
		echo "<br />You should enter the suburb in the box provided. Orders cannot be accepted without a valid suburb being entered.<br />";
	}

*/
// Check the date is OK
	if(isset($_POST['DeliveryDate']) and !Is_Date($_POST['DeliveryDate'])) {
		$InputErrors =1;
		prnMsg(_('An invalid date entry was made') . '. ' . _('The date entry must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
	}
// Check the date is OK
	if(isset($_POST['QuoteDate']) and !Is_Date($_POST['QuoteDate'])) {
		$InputErrors =1;
		prnMsg(_('An invalid date entry was made') . '. ' . _('The date entry must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
	}
// Check the date is OK
	if(isset($_POST['ConfirmedDate']) and !Is_Date($_POST['ConfirmedDate'])) {
		$InputErrors =1;
		 prnMsg(_('An invalid date entry was made') . '. ' . _('The date entry must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
	}

	 /* This check is not appropriate where orders need to be entered in retrospectively in some cases this check will be appropriate and this should be uncommented

	 elseif (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat'],$EarliestDispatch), $_POST['DeliveryDate'])){
		$InputErrors =1;
		echo '<br /><b>' . _('The delivery details cannot be updated because you are attempting to set the date the order is to be dispatched earlier than is possible. No dispatches are made on Saturday and Sunday. Also, the dispatch cut off time is') .  $_SESSION['DispatchCutOffTime']  . _(':00 hrs. Orders placed after this time will be dispatched the following working day.');
	}

	*/

	if ($InputErrors==0){

		if ($_SESSION['DoFreightCalc']==True){
			list ($_POST['FreightCost'], $BestShipper) = CalcFreightCost($_SESSION['Items'.$identifier]->total,
																		$_POST['BrAdd2'],
																		$_POST['BrAdd3'],
																		$_POST['BrAdd4'],
																		$_POST['BrAdd5'],
																		$_POST['BrAdd6'],
																		$_SESSION['Items'.$identifier]->totalVolume,
																		$_SESSION['Items'.$identifier]->totalWeight,
																		$_SESSION['Items'.$identifier]->Location,
																		$_SESSION['Items'.$identifier]->DefaultCurrency,
																		$db);
			if ( !empty($BestShipper) ){
				$_POST['FreightCost'] = round($_POST['FreightCost'],2);
				$_POST['ShipVia'] = $BestShipper;
			} else {
				prnMsg(_($_POST['FreightCost']),'warn');
			}
		}
		$sql = "SELECT custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.phoneno,
					custbranch.email,
					custbranch.defaultlocation,
					custbranch.defaultshipvia,
					custbranch.deliverblind,
					custbranch.specialinstructions,
					custbranch.estdeliverydays,
					custbranch.salesman
				FROM custbranch
				WHERE custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'
				AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

		$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_SESSION['Items'.$identifier]->CustomerName . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('SQL used to retrieve the branch details was') . ':';
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
		if (DB_num_rows($result)==0){

			prnMsg(_('The branch details for branch code') . ': ' . $_SESSION['Items'.$identifier]->Branch . ' ' . _('against customer code') . ': ' . $_POST['Select'] . ' ' . _('could not be retrieved') . '. ' . _('Check the set up of the customer and branch'),'error');

			if ($debug==1){
				echo '<br />' . _('The SQL that failed to get the branch details was') . ':<br />' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}
		if (!isset($_POST['SpecialInstructions'])) {
			$_POST['SpecialInstructions']='';
		}
		if (!isset($_POST['DeliveryDays'])){
			$_POST['DeliveryDays']=0;
		}
//		print_r($_SESSION['Items'.$identifier]);

		if (!isset($_SESSION['Items'.$identifier])) {
			$myrow = DB_fetch_row($result);
			$_SESSION['Items'.$identifier]->DeliverTo = $myrow[0];
			$_SESSION['Items'.$identifier]->DelAdd1 = $myrow[1];
			$_SESSION['Items'.$identifier]->DelAdd2 = $myrow[2];
			$_SESSION['Items'.$identifier]->DelAdd3 = $myrow[3];
			$_SESSION['Items'.$identifier]->DelAdd4 = $myrow[4];
			$_SESSION['Items'.$identifier]->DelAdd5 = $myrow[5];
			$_SESSION['Items'.$identifier]->DelAdd6 = $myrow[6];
			$_SESSION['Items'.$identifier]->PhoneNo = $myrow[7];
			$_SESSION['Items'.$identifier]->Email = $myrow[8];
			$_SESSION['Items'.$identifier]->Location = $myrow[9];
			$_SESSION['Items'.$identifier]->ShipVia = $myrow[10];
			$_SESSION['Items'.$identifier]->DeliverBlind = $myrow[11];
			$_SESSION['Items'.$identifier]->SpecialInstructions = $myrow[12];
			$_SESSION['Items'.$identifier]->DeliveryDays = $myrow[13];
			$_SESSION['Items'.$identifier]->SalesPerson = $myrow[14];
			$_SESSION['Items'.$identifier]->DeliveryDate = $_POST['DeliveryDate'];
			$_SESSION['Items'.$identifier]->QuoteDate = $_POST['QuoteDate'];
			$_SESSION['Items'.$identifier]->ConfirmedDate = $_POST['ConfirmedDate'];
			$_SESSION['Items'.$identifier]->CustRef = $_POST['CustRef'];
			$_SESSION['Items'.$identifier]->Comments = $_POST['Comments'];
			$_SESSION['Items'.$identifier]->FreightCost = round($_POST['FreightCost'],2);
			$_SESSION['Items'.$identifier]->advance = $_POST['advance'];
		$_SESSION['Items'.$identifier]->delivery = $_POST['delivery'];
		$_SESSION['Items'.$identifier]->commisioning = $_POST['commisioning'];
		$_SESSION['Items'.$identifier]->after = $_POST['after'];
		$_SESSION['Items'.$identifier]->gst = $_POST['GST'];
		$_SESSION['Items'.$identifier]->afterdays = $_POST['afterdays'];
		
			$_SESSION['Items'.$identifier]->Quotation = $_POST['Quotation'];
		} else {
			$_SESSION['Items'.$identifier]->DeliverTo = $_POST['DeliverTo'];
			$_SESSION['Items'.$identifier]->DelAdd1 = $_POST['BrAdd1'];
			$_SESSION['Items'.$identifier]->DelAdd2 = $_POST['BrAdd2'];
			$_SESSION['Items'.$identifier]->DelAdd3 = $_POST['BrAdd3'];
			$_SESSION['Items'.$identifier]->DelAdd4 = $_POST['BrAdd4'];
			$_SESSION['Items'.$identifier]->DelAdd5 = $_POST['BrAdd5'];
			$_SESSION['Items'.$identifier]->DelAdd6 = $_POST['BrAdd6'];
			$_SESSION['Items'.$identifier]->PhoneNo = $_POST['PhoneNo'];
			$_SESSION['Items'.$identifier]->Email = $_POST['Email'];
			$_SESSION['Items'.$identifier]->Location = $_POST['Location'];
			$_SESSION['Items'.$identifier]->ShipVia = $_POST['ShipVia'];
			$_SESSION['Items'.$identifier]->DeliverBlind = $_POST['DeliverBlind'];
			$_SESSION['Items'.$identifier]->SpecialInstructions = $_POST['SpecialInstructions'];
			$_SESSION['Items'.$identifier]->DeliveryDays = $_POST['DeliveryDays'];
			$_SESSION['Items'.$identifier]->DeliveryDate = $_POST['DeliveryDate'];
			$_SESSION['Items'.$identifier]->QuoteDate = $_POST['QuoteDate'];
			$_SESSION['Items'.$identifier]->ConfirmedDate = $_POST['ConfirmedDate'];
			$_SESSION['Items'.$identifier]->CustRef = $_POST['CustRef'];
			$_SESSION['Items'.$identifier]->Comments = $_POST['Comments'];
			$_SESSION['Items'.$identifier]->SalesPerson = $_POST['SalesPerson'];
			$_SESSION['Items'.$identifier]->FreightCost = round($_POST['FreightCost'],2);
						$_SESSION['Items'.$identifier]->advance = $_POST['advance'];
		$_SESSION['Items'.$identifier]->delivery = $_POST['delivery'];
		$_SESSION['Items'.$identifier]->commisioning = $_POST['commisioning'];
		$_SESSION['Items'.$identifier]->after = $_POST['after'];
		$_SESSION['Items'.$identifier]->gst = $_POST['GST'];
		$_SESSION['Items'.$identifier]->afterdays = $_POST['afterdays'];
		
			$_SESSION['Items'.$identifier]->Quotation = $_POST['Quotation'];
		}
		/*$_SESSION['DoFreightCalc'] is a setting in the config.php file that the user can set to false to turn off freight calculations if necessary */


		/* What to do if the shipper is not calculated using the system
		- first check that the default shipper defined in config.php is in the database
		if so use this
		- then check to see if any shippers are defined at all if not report the error
		and show a link to set them up
		- if shippers defined but the default shipper is bogus then use the first shipper defined
		*/
		if ((isset($BestShipper) AND $BestShipper=='') AND ($_POST['ShipVia']=='' OR !isset($_POST['ShipVia']))){
			$sql =  "SELECT shipper_id
						FROM shippers
						WHERE shipper_id='" . $_SESSION['Default_Shipper']."'";
			$ErrMsg = _('There was a problem testing for the default shipper');
			$DbgMsg = _('SQL used to test for the default shipper') . ':';
			$TestShipperExists = DB_query($sql,$db,$ErrMsg,$DbgMsg);

			if (DB_num_rows($TestShipperExists)==1){

				$BestShipper = $_SESSION['Default_Shipper'];

			} else {

				$sql = "SELECT shipper_id
							FROM shippers";
				$TestShipperExists = DB_query($sql,$db,$ErrMsg,$DbgMsg);

				if (DB_num_rows($TestShipperExists)>=1){
					$ShipperReturned = DB_fetch_row($TestShipperExists);
					$BestShipper = $ShipperReturned[0];
				} else {
					prnMsg(_('We have a problem') . ' - ' . _('there are no shippers defined'). '. ' . _('Please use the link below to set up shipping or freight companies') . ', ' . _('the system expects the shipping company to be selected or a default freight company to be used'),'error');
					echo '<a href="' . $RootPath . 'Shippers.php">' .  _('Enter') . '/' . _('Amend Freight Companies')  . '</a>';
				}
			}
			if (isset($_SESSION['Items'.$identifier]->ShipVia) AND $_SESSION['Items'.$identifier]->ShipVia!=''){
				$_POST['ShipVia'] = $_SESSION['Items'.$identifier]->ShipVia;
			} else {
				$_POST['ShipVia']=$BestShipper;
			}
		}
	}
}

if(isset($_POST['MakeRecurringOrder']) AND ! $InputErrors){

	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/Recurringocs.php?identifier='.$identifier   .  '&amp;NewRecurringOrder=Yes">';
	prnMsg(_('You should automatically be forwarded to the entry of recurring order details page') . '. ' . _('If this does not happen') . '(' . _('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/RecurringOrders.php?identifier='.$identifier  . '&amp;NewRecurringOrder=Yes">' .  _('click here')  . '</a> '. _('to continue'),'info');
	include('includes/footer.inc');
	exit;
}


if (isset($_POST['BackToLineDetails']) and $_POST['BackToLineDetails']==_('Modify Order Lines')){

	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/SelectOrderItemsOC.php?identifier='.$identifier   . '">';
	prnMsg(_('You should automatically be forwarded to the entry of the order line details page') . '. ' . _('If this does not happen') . '(' . _('if the browser does not support META Refresh') . ') '  . '<a href="' . $RootPath . '/SelectOrderItemsOC.php?identifier='.$identifier  . '">' .  _('click here')  . '</a> '. _('to continue'),'info');
	include('includes/footer.inc');
	exit;

}

If (isset($_POST['ProcessOrder'])) {
	/*Default OK_to_PROCESS to 1 change to 0 later if hit a snag */
	if ($InputErrors ==0) {
		$OK_to_PROCESS = 1;
	}
	If ($_POST['FreightCost'] != $OldFreightCost AND $_SESSION['DoFreightCalc']==True){
		$OK_to_PROCESS = 0;
		prnMsg(_('The freight charge has been updated') . '. ' . _('Please reconfirm that the order and the freight charges are acceptable and then confirm the order again if OK') .' <br /> '. _('The new freight cost is') .' ' . $_POST['FreightCost'] . ' ' . _('and the previously calculated freight cost was') .' '. $OldFreightCost,'warn');
	} else {

/*check the customer's payment terms */
		$sql = "SELECT daysbeforedue,
				dayinfollowingmonth
			FROM debtorsmaster,
				paymentterms
			WHERE debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtorsmaster.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

		$ErrMsg = _('The customer terms cannot be determined') . '. ' . _('This order cannot be processed because');
		$DbgMsg = _('SQL used to find the customer terms') . ':';
		$TermsResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);


		$myrow = DB_fetch_array($TermsResult);
		if ($myrow['daysbeforedue']==0 AND $myrow['dayinfollowingmonth']==0){

/* THIS IS A CASH SALE NEED TO GO OFF TO 3RD PARTY SITE SENDING MERCHANT ACCOUNT DETAILS AND CHECK FOR APPROVAL FROM 3RD PARTY SITE BEFORE CONTINUING TO PROCESS THE ORDER

UNTIL ONLINE CREDIT CARD PROCESSING IS PERFORMED ASSUME OK TO PROCESS

		NOT YET CODED     */

			$OK_to_PROCESS =1;


		} #end if cash sale detected

	} #end if else freight charge not altered
} #end if process order
//print_r($_SESSION['Items'.$identifier]->LineItems);
/*echo	$SQL='SELECT MAX(orderno) as orderno from salesorders where salescaseref="'.$_SESSION['salescaseref'].'"';
	$result=DB_query($SQL,$db);
	
	$row = mysqli_fetch_assoc($result);
echo	$SQL="UPDATE salesorderdetails SET quantityremaining` = (
	SELECT 
	salesorderdetails.quantity-ocdetails.quantity
	FROM salesorderdetails inner join ocs 
	on salesorderdetails.orderno = ocs.quotationno inner join ocdetails
	on ocdetails.orderno = ocs.orderno WHERE salesorderdetails.orderno = ". $row['orderno']."
    AND ocdetails.orderno = ".$OrderNo ."
	GROUP BY internalitemno
	
	
	
	)
	
	";
	DB_query($SQL,$db);
echo	$SQL="UPDATE salesorderoptions SET quantityremaining` = (
	SELECT 
	salesorderdetails.quantity-ocdetails.quantity
	FROM salesorderdetails inner join ocs 
	on salesorderdetails.orderno = ocs.quotationno inner join ocoptions
	on ocoptions.orderno = ocs.orderno WHERE salesorderdetails.orderno = ". $row['orderno']."
    AND ocdetails.orderno = ".$OrderNo ."
	GROUP BY internalitemno
	
	
	
	)
	
	";
	DB_query($SQL,$db);*/

if (isset($OK_to_PROCESS) AND $OK_to_PROCESS == 1 AND $_SESSION['ExistingOrder'.$identifier]==$identifier){

/* finally write the order header to the database and then the order line details */

	$DelDate = FormatDateforSQL($_SESSION['Items'.$identifier]->DeliveryDate);
	$QuotDate = FormatDateforSQL($_SESSION['Items'.$identifier]->QuoteDate);
	$ConfDate = FormatDateforSQL($_SESSION['Items'.$identifier]->ConfirmedDate);
	$sqlono = 'SELECT MAX(orderno) as maxorderno from ocs ';
	$resultono = DB_query($sqlono,$db);
	$rowono = DB_fetch_array($resultono);
	$Result = DB_Txn_Begin($db);


	
	$OrderNo = GetNextTransNo(60, $db);

	$OrderNoQ = GetNextTransNo(30, $db);

	//$OrderNo = $rowono['maxorderno'];
	
$SQL="SELECT pono FROM ocs where orderno='".$OrderNo."'";
$result=DB_query($SQL,$db);
$row=DB_fetch_array($result);
if ($row['pono']=='')	{
$HeaderSQL = "INSERT INTO salesorders (
								orderno,
								salescaseref,
								debtorno,
								branchcode,
								customerref,
								comments,
								orddate,
								ordertype,
								shipvia,
								deliverto,
								deladd1,
								deladd2,
								deladd3,
								deladd4,
								deladd5,
								deladd6,
								contactphone,
								contactemail,
								salesperson,
								freightcost,
								advance,
								delivery,
								commisioning,
								after,
								gst,
								afterdays,
								fromstkloc,
								deliverydate,
								quotedate,
								confirmeddate,
								quotation,
								deliverblind
								)
							VALUES (
								'". $OrderNoQ . "',
								'" . $_SESSION['salescaseref'] . "',
								
								'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
								
								'" . $_SESSION['Items'.$identifier]->Branch . "',
								'". DB_escape_string($_SESSION['Items'.$identifier]->CustRef) ."',
								'". DB_escape_string($_SESSION['Items'.$identifier]->Comments) ."',
								'" . Date("Y-m-d H:i") . "',
								'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
								'" . $_POST['ShipVia'] ."',
								'". DB_escape_string($_SESSION['Items'.$identifier]->DeliverTo) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd1) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd2) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd3) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd4) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd5) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd6) . "',
								'" . $_SESSION['Items'.$identifier]->PhoneNo . "',
								'" . $_SESSION['Items'.$identifier]->Email . "',
								'" . $_SESSION['Items'.$identifier]->SalesPerson . "',
								'" . $_SESSION['Items'.$identifier]->FreightCost ."',
								'" . $_SESSION['Items'.$identifier]->advance ."',
								'" . $_SESSION['Items'.$identifier]->delivery ."',
								'" . $_SESSION['Items'.$identifier]->commisioning ."',
								'" . $_SESSION['Items'.$identifier]->after ."',
									'" . $_SESSION['Items'.$identifier]->gst ."',
								
								'" . $_SESSION['Items'.$identifier]->afterdays ."',
								
								'" . $_SESSION['Items'.$identifier]->Location ."',
								'" . $DelDate . "',
								'" . $QuotDate . "',
								'" . $ConfDate . "',
								1,
								'" . $_SESSION['Items'.$identifier]->DeliverBlind ."'
								)";
								
	$ErrMsg = _('The order cannot be added because');

	$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);

}
	$HeaderSQL = "INSERT INTO ocs (
								orderno,
								salescaseref,
								debtorno,
								branchcode,
								customerref,
								comments,
								orddate,
								ordertype,
								shipvia,
								deliverto,
								deladd1,
								deladd2,
								deladd3,
								deladd4,
								deladd5,
								deladd6,
								contactphone,
								contactemail,
								salesperson,
								freightcost,
								advance,
								delivery,
								commisioning,
								after,
								gst,
								afterdays,
								fromstkloc,
								deliverydate,
								quotedate,
								confirmeddate,
								quotation,
								deliverblind)
							VALUES (
								'". $OrderNo . "',
								'" . $_SESSION['salescaseref'] . "',
								
								'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
								
								'" . $_SESSION['Items'.$identifier]->Branch . "',
								'". DB_escape_string($_SESSION['Items'.$identifier]->CustRef) ."',
								'". DB_escape_string($_SESSION['Items'.$identifier]->Comments) ."',
								'" . Date("Y-m-d H:i") . "',
								'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
								'" . $_POST['ShipVia'] ."',
								'". DB_escape_string($_SESSION['Items'.$identifier]->DeliverTo) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd1) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd2) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd3) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd4) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd5) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd6) . "',
								'" . $_SESSION['Items'.$identifier]->PhoneNo . "',
								'" . $_SESSION['Items'.$identifier]->Email . "',
								'" . $_SESSION['Items'.$identifier]->SalesPerson . "',
								'" . $_SESSION['Items'.$identifier]->FreightCost ."',
								'" . $_SESSION['Items'.$identifier]->advance ."',
								'" . $_SESSION['Items'.$identifier]->delivery ."',
								'" . $_SESSION['Items'.$identifier]->commisioning ."',
								'" . $_SESSION['Items'.$identifier]->after ."',
									'" . $_SESSION['Items'.$identifier]->gst ."',
								
								'" . $_SESSION['Items'.$identifier]->afterdays ."',
								
								'" . $_SESSION['Items'.$identifier]->Location ."',
								'" . $DelDate . "',
								'" . $QuotDate . "',
								'" . $ConfDate . "',
								1,
								'" . $_SESSION['Items'.$identifier]->DeliverBlind ."'
								)";

	$ErrMsg = _('The order cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);
//insert replica quote


	//end insert replica quote

	//external lines
	$StartOf_ExternalLinesSQL = "INSERT INTO oclines (
											
											
														lineno,
														orderno,
														clientrequirements)
										VALUES (";
	$DbgMsg = _('The SQL that failed was');
	//print_r($_SESSION['Items'.$identifier]);
	foreach ($_SESSION['Items'.$identifier]->externallines as $externalline) {

	$ExternalLinesSQL = $StartOf_ExternalLinesSQL ."
					'" . $externalline->externallineno . "',
					'" . $OrderNo. "',
					'" . $externalline->clientreq . "'
					
				)";
	 
		$ErrMsg = _('Unable to add the sales order line');
		$Ins_ExternalLinesResult = DB_query($ExternalLinesSQL,$db,$ErrMsg,$DbgMsg,true);
		
		$StartOf_LineOptionSQL = "INSERT INTO ocoptions (
											
															orderno,
														lineno,
														optionno,
														optiontext,
														stockstatus,
														quantity
														)
										VALUES (";
	$DbgMsg = _('The SQL that failed was');
	//print_r($_SESSION['Items'.$identifier]);
	
	foreach ($externalline->lineoptions as $lineoption) {

	$LineOptionSQL = $StartOf_LineOptionSQL ."
					
					'" . $OrderNo. "',
					'" . $lineoption->externallineno . "',
					'" . $lineoption->optionno . "',
					'" . $lineoption->optiontext . "',
					'" . $lineoption->stockstatus . "',
					'" . $lineoption->quantity . "'
				
				
				)";
	}
//	echo $ExternalLinesSQL;
		$ErrMsg = _('Unable to add the sales order line');
		$Ins_LineOptionResult = DB_query($LineOptionSQL,$db,$ErrMsg,$DbgMsg,true);
	
	
	}
	//end external lines
	$StartOf_LineItemsSQL = "INSERT INTO ocdetails (
											internalitemno,
														orderlineno,
														lineoptionno,
														optionitemno,
							
														orderno,
														stkcode,
														quantity,
														
														unitprice,
														discountpercent,
														itemdue,
														poline
														)
										VALUES (";
	$DbgMsg = _('The SQL that failed was');
	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {
$SQL="SELECT quantity as optionqty FROM ocoptions WHERE
		lineno=".$StockItem->orderlineno." AND optionno=".$StockItem->lineoptionno."
		AND orderno=".$_SESSION['ExistingOrder'.$identifier]."
		";
	
	$result=DB_query($SQL,$db);
	$row=DB_fetch_array($result);
	$LineItemsSQL = $StartOf_LineItemsSQL ."
					'" . $StockItem->LineNumber . "',
					'" . $StockItem->orderlineno. "',
					'" . $StockItem->lineoptionno . "',
					'" . $StockItem->optionitemno . "',
					'" . $OrderNo . "',
					'" . $StockItem->StockID . "',
					'" . $StockItem->Quantity . "',
					
					'" . $StockItem->Price . "',
					
					'" . floatval($StockItem->DiscountPercent) . "',
				'" . FormatDateForSQL($StockItem->ItemDue) . "',
					'" . $StockItem->POLine . "'
					
				
				)";
	}
		$ErrMsg = _('Unable to add the sales order line');
	
		$Ins_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);
//QUOTE DETAILS LINES OPTIONS
	//external lines

$SQL="SELECT pono FROM ocs where orderno='".$OrderNo."'";
$result=DB_query($SQL,$db);
$row=DB_fetch_array($result);
if ($row['pono']=='')	{

	



	$StartOf_ExternalLinesSQL = "INSERT INTO salesorderlines (
											
											
														lineno,
														orderno,
														clientrequirements)
										VALUES (";
	$DbgMsg = _('The SQL that failed was');
	//print_r($_SESSION['Items'.$identifier]);
	foreach ($_SESSION['Items'.$identifier]->externallines as $externalline) {

	$ExternalLinesSQL = $StartOf_ExternalLinesSQL ."
					'" . $externalline->externallineno . "',
					'" . $OrderNoQ. "',
					'" . $externalline->clientreq . "'
					
				)";
	}
		$ErrMsg = _('Unable to add the sales order line');
		$Ins_ExternalLinesResult = DB_query($ExternalLinesSQL,$db,$ErrMsg,$DbgMsg,true);
		
		$StartOf_LineOptionSQL = "INSERT INTO salesorderoptions (
											
															orderno,
														lineno,
														optionno,
														optiontext,
														stockstatus,
														quantity
														)
										VALUES (";
	$DbgMsg = _('The SQL that failed was');
	//print_r($_SESSION['Items'.$identifier]);
	
	foreach ($externalline->lineoptions as $lineoption) {

	$LineOptionSQL = $StartOf_LineOptionSQL ."
					
					'" . $OrderNoQ. "',
					'" . $lineoption->externallineno . "',
					'" . $lineoption->optionno . "',
					'" . $lineoption->optiontext . "',
					'" . $lineoption->stockstatus . "',
					'" . $lineoption->quantity . "'
				
				
				)";
	}
//	echo $ExternalLinesSQL;
		$ErrMsg = _('Unable to add the sales order line');
		$Ins_LineOptionResult = DB_query($LineOptionSQL,$db,$ErrMsg,$DbgMsg,true);
	}
	
	}
	//end external lines
	$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (
											internalitemno,
														orderlineno,
														lineoptionno,
														optionitemno,
							
														orderno,
														stkcode,
														quantity,
														unitprice,
														discountpercent,
														itemdue,
														poline
														)
										VALUES (";
	$DbgMsg = _('The SQL that failed was');
	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {
$SQL="SELECT quantity as optionqty FROM salesorderoptions WHERE
		lineno=".$StockItem->orderlineno." AND optionno=".$StockItem->lineoptionno."
		AND orderno=".$_SESSION['ExistingOrder'.$identifier]."
		";
	$result=DB_query($SQL,$db);
	$row=DB_fetch_array($result);
	$LineItemsSQL = $StartOf_LineItemsSQL ."
					'" . $StockItem->LineNumber . "',
					'" . $StockItem->orderlineno. "',
					'" . $StockItem->lineoptionno . "',
					'" . $StockItem->optionitemno . "',
					'" . $OrderNoQ . "',
					'" . $StockItem->StockID . "',
					'" . $StockItem->Quantity . "',
					'" . $StockItem->Price . "',
					
					'" . floatval($StockItem->DiscountPercent) . "',
				'" . FormatDateForSQL($StockItem->ItemDue) . "',
					'" . $StockItem->POLine . "'
					
				)";
	}
		$ErrMsg = _('Unable to add the sales order line');
	
		$Ins_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);
	


}


//END QUOTE DETAILS LINES OPTIONS
//update quantity remaining quotation


		/*Now check to see if the item is manufactured
		 * 			and AutoCreateWOs is on
		 * 			and it is a real order (not just a quotation)*/

		if ($StockItem->MBflag=='M'
			AND $_SESSION['AutoCreateWOs']==1
			AND $_SESSION['Items'.$identifier]->Quotation!=1){ //oh yeah its all on!

			echo '<br />';

			//now get the data required to test to see if we need to make a new WO
			$QOHResult = DB_query("SELECT SUM(quantity) FROM locstock WHERE stockid='" . $StockItem->StockID . "'",$db);
			$QOHRow = DB_fetch_row($QOHResult);
			$QOH = $QOHRow[0];

		$SQL = "SELECT SUM(ocdetails.quantity - ocdetails.qtyinvoiced) AS qtydemand
					FROM ocdetails INNER JOIN ocs
					ON ocdetails.orderno=ocs.orderno
					WHERE ocdetails.stkcode = '" . $StockItem->StockID . "'
					AND ocdetails.completed = 0
					
					AND ocs.quotation=0";
			$DemandResult = DB_query($SQL,$db);
			$DemandRow = DB_fetch_row($DemandResult);
			$QuantityDemand = $DemandRow[0];

			$SQL = "SELECT SUM((ocdetails.quantity-ocdetails.qtyinvoiced)*bom.quantity) AS dem
					FROM ocdetails INNER JOIN ocs
					ON ocdetails.orderno=ocs.orderno
					INNER JOIN bom ON ocdetails.stkcode=bom.parent
					INNER JOIN stockmaster ON stockmaster.stockid=bom.parent
					WHERE ocdetails.quantity-ocdetails.qtyinvoiced > 0
					AND bom.component='" . $StockItem->StockID . "'
					AND ocs.quotation=0
					
					AND stockmaster.mbflag='A'
					AND ocdetails.completed=0";
			$AssemblyDemandResult = DB_query($SQL,$db);
			$AssemblyDemandRow = DB_fetch_row($AssemblyDemandResult);
			$QuantityAssemblyDemand = $AssemblyDemandRow[0];

			$SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
					FROM purchorderdetails,
						purchorders
					WHERE purchorderdetails.orderno = purchorders.orderno
					AND purchorderdetails.itemcode = '" . $StockItem->StockID . "'
					AND purchorderdetails.completed = 0";
			$PurchOrdersResult = DB_query($SQL,$db);
			$PurchOrdersRow = DB_fetch_row($PurchOrdersResult);
			$QuantityPurchOrders = $PurchOrdersRow[0];

			$SQL = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) as qtyonorder
					FROM woitems INNER JOIN workorders
					ON woitems.wo=workorders.wo
					WHERE woitems.stockid = '" . $StockItem->StockID . "'
					AND woitems.qtyreqd > woitems.qtyrecd
					AND workorders.closed = 0";
			$WorkOrdersResult = DB_query($SQL,$db);
			$WorkOrdersRow = DB_fetch_row($WorkOrdersResult);
			$QuantityWorkOrders = $WorkOrdersRow[0];

			//Now we have the data - do we need to make any more?
			$ShortfallQuantity = $QOH-$QuantityDemand-$QuantityAssemblyDemand+$QuantityPurchOrders+$QuantityWorkOrders;

			if ($ShortfallQuantity < 0) { //then we need to make a work order
				//How many should the work order be for??
				if ($ShortfallQuantity + $StockItem->EOQ < 0){
					$WOQuantity = -$ShortfallQuantity;
				} else {
					$WOQuantity = $StockItem->EOQ;
				}

				$WONo = GetNextTransNo(40,$db);
				$ErrMsg = _('Unable to insert a new work order for the sales order item');
				$InsWOResult = DB_query("INSERT INTO workorders (wo,
												 loccode,
												 requiredby,
												 startdate)
								 VALUES ('" . $WONo . "',
										'" . $_SESSION['DefaultFactoryLocation'] . "',
										'" . Date('Y-m-d') . "',
										'" . Date('Y-m-d'). "')",
										$db,$ErrMsg,$DbgMsg,true);
				//Need to get the latest BOM to roll up cost
				$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
													FROM stockmaster INNER JOIN bom
													ON stockmaster.stockid=bom.component
													WHERE bom.parent='" . $StockItem->StockID . "'
													AND bom.loccode='" . $_SESSION['DefaultFactoryLocation'] . "'",
										$db);
				$CostRow = DB_fetch_row($CostResult);
				if (is_null($CostRow[0]) OR $CostRow[0]==0){
					$Cost =0;
					prnMsg(_('In automatically creating a work order for') . ' ' . $StockItem->StockID . ' ' . _('an item on this sales order, the cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
				} else {
					$Cost = $CostRow[0];
				}

				// insert parent item info
				$sql = "INSERT INTO woitems (wo,
											 stockid,
											 qtyreqd,
											 stdcost)
								 VALUES ( '" . $WONo . "',
										 '" . $StockItem->StockID . "',
										 '" . $WOQuantity . "',
										 '" . $Cost . "')";
				$ErrMsg = _('The work order item could not be added');
				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

				//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
				WoRealRequirements($db, $WONo, $_SESSION['DefaultFactoryLocation'], $StockItem->StockID);

				$FactoryManagerEmail = _('A new work order has been created for') .
									":\n" . $StockItem->StockID . ' - ' . $StockItem->ItemDescription . ' x ' . $WOQuantity . ' ' . $StockItem->Units .
									"\n" . _('These are for') . ' ' . $_SESSION['Items'.$identifier]->CustomerName . ' ' . _('there order ref') . ': '  . $_SESSION['Items'.$identifier]->CustRef . ' ' ._('our order number') . ': ' . $OrderNo;

				if ($StockItem->Serialised AND $StockItem->NextSerialNo>0){
						//then we must create the serial numbers for the new WO also
						$FactoryManagerEmail .= "\n" . _('The following serial numbers have been reserved for this work order') . ':';

						for ($i=0;$i<$WOQuantity;$i++){

							$result = DB_query("SELECT serialno FROM stockserialitems
												WHERE serialno='" . ($StockItem->NextSerialNo + $i) . "'
												AND stockid='" . $StockItem->StockID ."'",$db);
							if (DB_num_rows($result)!=0){
								$WOQuantity++;
								prnMsg(($StockItem->NextSerialNo + $i) . ': ' . _('This automatically generated serial number already exists - it cannot be added to the work order'),'error');
							} else {
								$sql = "INSERT INTO woserialnos (wo,
																stockid,
																serialno)
													VALUES ('" . $WONo . "',
															'" . $StockItem->StockID . "',
															'" . ($StockItem->NextSerialNo + $i) . "')";
								$ErrMsg = _('The serial number for the work order item could not be added');
								$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
								$FactoryManagerEmail .= "\n" . ($StockItem->NextSerialNo + $i);
							}
						} //end loop around creation of woserialnos
						$NewNextSerialNo = ($StockItem->NextSerialNo + $WOQuantity +1);
						$ErrMsg = _('Could not update the new next serial number for the item');
						$UpdateNextSerialNoResult = DB_query("UPDATE stockmaster SET nextserialno='" . $NewNextSerialNo . "' WHERE stockid='" . $StockItem->StockID . "'",$db,$ErrMsg,$DbgMsg,true);
				} // end if the item is serialised and nextserialno is set

				$EmailSubject = _('New Work Order Number') . ' ' . $WONo . ' ' . _('for') . ' ' . $StockItem->StockID . ' x ' . $WOQuantity;
				//Send email to the Factory Manager
				if($_SESSION['SmtpSetting']==0){
					mail($_SESSION['FactoryManagerEmail'],$EmailSubject,$FactoryManagerEmail);

				}else{
					include('includes/htmlMimeMail.php');
					$mail = new htmlMimeMail();
					$mail->setSubject($EmailSubject);
					$result = SendmailBySmtp($mail,array($_SESSION['FactoryManagerEmail']));
				}

			} //end if with this sales order there is a shortfall of stock - need to create the WO
		}//end if auto create WOs in on
	} /* end inserted line items into sales order details */

	$result = DB_Txn_Commit($db);
	echo '<br />';


	if ($_SESSION['Items'.$identifier]->Quotation==1){
		prnMsg(_('Quotation Number') . ' ' . $OrderNo . ' ' . _('has been entered'),'success');
	} else {
		prnMsg(_('Order Number') . ' ' . $OrderNo . ' ' . _('has been entered'),'success');
	}

	if (count($_SESSION['AllowedPageSecurityTokens'])>1){
		/* Only allow print of order confirmation note for internal staff - customer logon's cannot go here */

		if ($_POST['Quotation']==0) { /*then its not a quotation its a real order */

			echo '<br /><table class="selection">
					<tr>
						<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" /></td>
						<td>' . ' ' . '<a target="_blank" href="' . $RootPath . '/PrintCustOrder.php?identifier='.$identifier . '&amp;TransNo=' . $OrderNo . '">' .  _('Print order confirmation note') . ' (' . _('Preprinted stationery') . ')'  . '</a></td>
					</tr>';
			echo '<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" /></td>
					<td>' . ' ' . '<a  target="_blank" href="' . $RootPath . '/PrintCustOrder_generic.php?identifier='.$identifier . '&amp;TransNo=' . $OrderNo . '">' .  _('Print order confirmation note') . ' (' . _('Laser') . ')'  . '</a></td>
				</tr>';

			echo '<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . _('Invoice') . '" alt="" /></td>
					<td>' . ' ' . '<a href="' . $RootPath . '/ConfirmDispatch_Invoice.php?identifier='.$identifier . '&amp;OrderNumber=' . $OrderNo .'">' .  _('Confirm Dispatch and Produce Invoice')  . '</a></td>
				</tr>';

			echo '</table>';

		} else {
			/*link to print the quotation */
			echo '<br /><table class="selection">
					<tr>
						<td><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . _('Order') . '" alt=""></td>
						<td>' . ' ' . '<a href="' . $RootPath . '/PDFOC.php?identifier='.$identifier . '&amp;OrderConfirmationNo=' . $OrderNo . '">' .  _('Print Order (Internal)')  . '</a></td>
					</tr>
					</table>';
			echo '<br /><table class="selection">
					<tr>
						<td><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . _('Order') . '" alt="" /></td>
						<td>' . ' ' . '<a href="' . $RootPath . '/PDFOCExternal.php?identifier='.$identifier . '&amp;OrderConfirmationNo=' . $OrderNo . '">' .  _('Print Order (External)')  . '</a></td>
					</tr>
					</table>';
		}
		echo '<br /><table class="selection">
			
				</table>';
	} else {
		/*its a customer logon so thank them */
		prnMsg(_('Thank you for your business'),'success');
	}
	
	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);

	include('includes/footer.inc');
	exit;

} elseif (isset($OK_to_PROCESS) AND ($OK_to_PROCESS == 1 AND $_SESSION['ExistingOrder'.$identifier]!=0)){

/* update the order header then update the old order line details and insert the new lines */

	$DelDate = FormatDateforSQL($_SESSION['Items'.$identifier]->DeliveryDate);
	$QuotDate = FormatDateforSQL($_SESSION['Items'.$identifier]->QuoteDate);
	$ConfDate = FormatDateforSQL($_SESSION['Items'.$identifier]->ConfirmedDate);

	$Result = DB_Txn_Begin($db);

	/*see if this is a contract quotation being changed to an order? */
	if ($_SESSION['Items'.$identifier]->Quotation==0) { //now its being changed? to an order
		$ContractResult = DB_query("SELECT contractref,
											requireddate
									FROM contracts WHERE orderno='" .  $_SESSION['ExistingOrder'.$identifier] ."'
									AND status=1",$db);
		if (DB_num_rows($ContractResult)==1){ //then it is a contract quotation being changed to an order
			$ContractRow = DB_fetch_array($ContractResult);
			$WONo = GetNextTransNo(40,$db);
			$ErrMsg = _('Could not update the contract status');
			$DbgMsg = _('The SQL that failed to update the contract status was');
			$UpdContractResult=DB_query("UPDATE contracts SET status=2,
															wo='" . $WONo . "'
										WHERE orderno='" .$_SESSION['ExistingOrder'.$identifier] . "'", $db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('Could not insert the contract bill of materials');
			$InsContractBOM = DB_query("INSERT INTO bom (parent,
														 component,
														 workcentreadded,
														 loccode,
														 effectiveafter,
														 effectiveto,
													 	 quantity)
											SELECT contractref,
													stockid,
													workcentreadded,
													'" . $_SESSION['Items'.$identifier]->Location ."',
													'" . Date('Y-m-d') . "',
													'2099-12-31',
													quantity
											FROM contractbom
											WHERE contractref='" . $ContractRow['contractref'] . "'",$db,$ErrMsg,$DbgMsg);

			$ErrMsg = _('Unable to insert a new work order for the sales order item');
			$InsWOResult = DB_query("INSERT INTO workorders (wo,
															 loccode,
															 requiredby,
															 startdate)
											 VALUES ('" . $WONo . "',
													'" . $_SESSION['Items'.$identifier]->Location ."',
													'" . $ContractRow['requireddate'] . "',
													'" . Date('Y-m-d'). "')",
													$db,$ErrMsg,$DbgMsg);
			//Need to get the latest BOM to roll up cost but also add the contract other requirements
			$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*contractbom.quantity) AS cost
									FROM stockmaster INNER JOIN contractbom
									ON stockmaster.stockid=contractbom.stockid
									WHERE contractbom.contractref='" .  $ContractRow['contractref'] . "'",
									$db);
			$CostRow = DB_fetch_row($CostResult);
			if (is_null($CostRow[0]) OR $CostRow[0]==0){
				$Cost =0;
				prnMsg(_('In automatically creating a work order for') . ' ' . $ContractRow['contractref'] . ' ' . _('an item on this sales order, the cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
			} else {
				$Cost = $CostRow[0]; //cost of contract BOM
			}
			$CostResult = DB_query("SELECT SUM(costperunit*quantity) AS cost
									FROM contractreqts
									WHERE contractreqts.contractref='" .  $ContractRow['contractref'] . "'",
									$db);
			$CostRow = DB_fetch_row($CostResult);
			//add other requirements cost to cost of contract BOM
			$Cost += $CostRow[0];

			// insert parent item info
			$sql = "INSERT INTO woitems (wo,
										 stockid,
										 qtyreqd,
										 stdcost)
							 VALUES ( '" . $WONo . "',
									 '" . $ContractRow['contractref'] . "',
									 '1',
									 '" . $Cost . "')";
			$ErrMsg = _('The work order item could not be added');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

			//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
			WoRealRequirements($db, $WONo, $_SESSION['Items'.$identifier]->Location, $ContractRow['contractref']);

		} //end processing if the order was a contract quotation being changed to an order
	} //end test to see if the order was a contract quotation being changed to an order


	$HeaderSQL = "UPDATE ocs SET debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
										branchcode = '" . $_SESSION['Items'.$identifier]->Branch . "',
										customerref = '". DB_escape_string($_SESSION['Items'.$identifier]->CustRef) ."',
										comments = '". DB_escape_string($_SESSION['Items'.$identifier]->Comments) ."',
										ordertype = '" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
										shipvia = '" . $_POST['ShipVia'] . "',
										deliverydate = '" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->DeliveryDate)) . "',
										quotedate = '" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->QuoteDate)) . "',
										confirmeddate = '" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->ConfirmedDate)) . "',
										deliverto = '" . DB_escape_string($_SESSION['Items'.$identifier]->DeliverTo) . "',
										deladd1 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd1) . "',
										deladd2 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd2) . "',
										deladd3 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd3) . "',
										deladd4 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd4) . "',
										deladd5 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd5) . "',
										deladd6 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd6) . "',
										contactphone = '" . $_SESSION['Items'.$identifier]->PhoneNo . "',
										contactemail = '" . $_SESSION['Items'.$identifier]->Email . "',
										salesperson = '" .  $_SESSION['Items'.$identifier]->SalesPerson . "',
										freightcost = '" . $_SESSION['Items'.$identifier]->FreightCost ."',
										
										advance = '" . $_SESSION['Items'.$identifier]->advance ."',
										delivery = '" . $_SESSION['Items'.$identifier]->delivery ."',
										commisioning = '" . $_SESSION['Items'.$identifier]->commisioning ."',
										after = '" . $_SESSION['Items'.$identifier]->after ."',
										gst = '" . $_SESSION['Items'.$identifier]->gst ."',
										afterdays = '" . $_SESSION['Items'.$identifier]->afterdays ."',
										
										
										
										fromstkloc = '" . $_SESSION['Items'.$identifier]->Location ."',
										
										printedpackingslip = '" . $_POST['ReprintPackingSlip'] . "',
										quotation = '" . $_SESSION['Items'.$identifier]->Quotation . "',
										deliverblind = '" . $_SESSION['Items'.$identifier]->DeliverBlind . "'
						WHERE ocs.orderno='" . $_SESSION['ExistingOrder'.$identifier] ."'";

	$DbgMsg = _('The SQL that was used to update the order and failed was');
	$ErrMsg = _('The order cannot be updated because');
	$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);


	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

		/* Check to see if the quantity reduced to the same quantity
		as already invoiced - so should set the line to completed */
		if ($StockItem->Quantity == $StockItem->QtyInv){
			$Completed = 1;
		} else {  /* order line is not complete */
			$Completed = 0;
		}
		//print_r($StockItem);
	$SQL="SELECT quantity as optionqty FROM ocoptions WHERE
		lineno=".$StockItem->orderlineno." AND optionno=".$StockItem->lineoptionno."
		AND orderno=".$_SESSION['ExistingOrder'.$identifier]."
		";
	$result=DB_query($SQL,$db);
	$row=DB_fetch_array($result);
	$LineItemsSQL = "UPDATE ocdetails SET unitprice='" . $StockItem->Price . "',
													quantity='" . $StockItem->Quantity . "',
													quantityremaining='" . $StockItem->Quantity
													*$row['optionqty']
													. "',
													discountpercent='" . floatval($StockItem->DiscountPercent) . "',
													completed='" . $Completed . "',
													poline='" . $StockItem->POLine . "',
													itemdue='" . FormatDateForSQL($StockItem->ItemDue) . "'
						WHERE ocdetails.orderno='" . $_SESSION['ExistingOrder'.$identifier] . "'
						AND ocdetails.internalitemno='" . $StockItem->LineNumber . "'";

		$DbgMsg = _('The SQL that was used to modify the order line and failed was');
		$ErrMsg = _('The updated order line cannot be modified because');
		$Upd_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);
		$SQL='SELECT MAX(orderno) as orderno from salesorders where salescaseref="'.$_SESSION['salescaseref'].'"';
	$result=DB_query($SQL,$db);
	
	$row = mysqli_fetch_assoc($result);
	$SQL='SELECT * FROM ocs WHERE ocs.orderno = "' . $_SESSION['ExistingOrder'.$identifier] . '"';
	$result=DB_query($SQL,$db);
	$row=mysqli_fetch_assoc($result);
	
	} /* updated line items into sales order details */

	$Result=DB_Txn_Commit($db);
	$Quotation = $_SESSION['Items'.$identifier]->Quotation;
	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);

	if($Quotation){ //handle Quotations and Orders print after modification
//		echo $SQL="SELECT pono FROM ocs where orderno='".$_SESSION['ExistingOrder'.$identifier]."'";
		prnMsg(_('Order Number') .' ' . $_SESSION['ExistingOrder'.$identifier] . ' ' . _('has been updated'),'success');

		/*link to print the quotation */
		echo '<br /><table class="selection">
				<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . _('Order') . '" alt=""></td>
					<td>' . ' ' . '<a href="' . $RootPath . '/PDFOC.php?identifier='.$identifier . '&amp;OrderConfirmationNo=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  _('Print Order Confirmation')  . '</a></td>
				</tr>
				</table>';
		echo '<br /><table class="selection">
				<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . _('Order') . '" alt="" /></td>
					<td>' . ' ' . '<a href="' . $RootPath . '/PDFOCExternal.php?identifier='.$identifier . '&amp;OrderConfirmationNo=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  _('Print Order Confirmation (External)')  . '</a></td>
				</tr>
				</table>';
	}else{

	prnMsg(_('Order Number') .' ' . $_SESSION['ExistingOrder'.$identifier] . ' ' . _('has been updated'),'success');

	echo '<br />
			<table class="selection">
			<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" /></td>
			<td><a target="_blank" href="' . $RootPath . '/PrintCustOrder.php?identifier='.$identifier  . '&amp;TransNo=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  _('Print order confirmation note - pre-printed stationery')  . '</a></td>
			</tr>';
	echo '<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" /></td>
			<td><a  target="_blank" href="' . $RootPath . '/PrintCustOrder_generic.php?identifier='.$identifier  . '&amp;TransNo=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  _('Print order confirmation note') . ' (' . _('Laser') . ')'  . '</a></td>
		</tr>';
	echo '<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . _('Invoice') . '" alt="" /></td>
			<td><a href="' . $RootPath .'/ConfirmDispatch_Invoice.php?identifier='.$identifier  . '&amp;OrderNumber=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  _('Confirm Order Delivery Quantities and Produce Invoice')  . '</a></td>
		</tr>';
	echo '<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/sales.png" title="' . _('Order') . '" alt="" /></td>
			<td><a href="' . $RootPath .'/Selectoc.php?identifier='.$identifier   . '">' .  _('Select A Different Order')  . '</a></td>
		</tr>
		</table>';
	}//end of print orders
	include('includes/footer.inc');
	exit;
}


if (isset($_SESSION['Items'.$identifier]->SpecialInstructions) and mb_strlen($_SESSION['Items'.$identifier]->SpecialInstructions)>0) {
	prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'info');
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Delivery') . '" alt="" />' . ' ' . _('Delivery Details') . '</p>';

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Customer Code') . ' :<b> ' . $_SESSION['Items'.$identifier]->DebtorNo . '<br />';
echo '</b>&nbsp;' . _('Customer Name') . ' :<b> ' . $_SESSION['Items'.$identifier]->CustomerName . '</b></p>';


echo '<form action="' . $_SERVER['PHP_SELF'] . '?identifier='.$identifier  . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$sqlL="select MAX(orderlineno) as maxlineno from ocdetails where 
	
	orderno = ".$_SESSION['ExistingOrder' .$identifier]."
	
	"; 		
$result = DB_query($sqlL,$db);
$resultrow=DB_fetch_array($result);
//echo $resultrow['maxlineno'];

for ($line=0; $line<= $resultrow['maxlineno']; $line++ )
{
	$sqllinecheck = "SELECT *
	FROM ocdetails 
	WHERE ocdetails.orderno='" . $_SESSION['ExistingOrder' .$identifier] . "'
	AND ocdetails.orderlineno='" .$line. "'

	";
					$resultlinecheck = DB_query($sqllinecheck,$db);
		//	$TransferRowB = DB_fetch_array($resultB);
	if(mysqli_num_rows($resultlinecheck)>0)
	{
	$sqlB = "SELECT *
	FROM oclines 
	WHERE oclines.orderno='" . $_SESSION['ExistingOrder' .$identifier] . "'
	AND oclines.lineno='" .$line. "'
	";
					$resultB = DB_query($sqlB,$db);
			$TransferRowB = DB_fetch_array($resultB);
$sqlO="select MAX(lineoptionno) as maxlineoptionno from ocdetails where orderno = ".$_SESSION['ExistingOrder' .$identifier]."

AND orderlineno = ".$line.""; 		
$resultO = DB_query($sqlO,$db);
$resultrowO=DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";
		echo '<table><tr><th colspan="7">' . '<h3>LINE '. $line+1 .'</h3><hr><b>Client Required:</b> <br />'.html_entity_decode($TransferRowB['clientrequirements']). '</th></tr></table>';
	}
for ($option=0; $option<= $resultrowO['maxlineoptionno']; $option++ )
	{
		$sqloptioncheck = "SELECT *
	FROM ocdetails 
	WHERE ocdetails.orderno='" . $_SESSION['ExistingOrder' .$identifier]. "'
	AND ocdetails.orderlineno='" .$line. "'
	AND ocdetails.lineoptionno='" .$option. "'
	
	
	";
					$resultoptioncheck = DB_query($sqloptioncheck,$db);
//	echo	'<b>'.mysqli_num_rows($resultoptioncheck).'</b>';			
		//	$TransferRowC = DB_fetch_array($resultC);
if(mysqli_num_rows($resultoptioncheck)>0)
{
	$sqlC = "SELECT *
	FROM ocoptions 
	WHERE ocoptions.orderno='" . $_SESSION['ExistingOrder' .$identifier]. "'
	AND ocoptions.lineno='" .$line. "'
	AND ocoptions.optionno='" .$option. "'
	
	";
					$resultC = DB_query($sqlC,$db);
			$TransferRowC = DB_fetch_array($resultC);
				$SQL='SELECT * FROM ocs WHERE ocs.orderno = "' .$_SESSION['ExistingOrder'.$identifier] . '"';
	$result=DB_query($SQL,$db);
	$row=mysqli_fetch_assoc($result);
	if($row['optionupdatecheck']<=($line+1)*($option+1))
	{
	$sqllo = "UPDATE salesorderoptions SET 
													quantityremaining=quantityremaining-" . $TransferRowC['quantity'] . "
															
								WHERE optionno=".$option." AND
								orderno=" . $row['quotationno'] . " AND
								lineno=" . $line;							
								
	//		$result = DB_query($sqllo, $db
		//		, _('The option number') . ' ' . $optionindex .  ' ' . _('could not be updated'));
	$SQL='UPDATE ocs SET optionupdatecheck=updatecheck+1 WHERE ocs.orderno = "' . $_SESSION['ExistingOrder'.$identifier]. '"';
		DB_query($SQL,$db);
	}
			
			
			
		//echo "<h3>Line No. ".($i+1)."</h3><br>";
	//	echo "<h3>Line No. ".($i+1)." Option No. ".($j+1)."</h3>";
		echo "<br>";

		//echo'	<table width="90%" cellpadding="2">';
/*Display the order with or without discount depending on access level*/
//if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
//print_r($_SESSION['Items'.$identifier]->LineItems);
if(1){
	echo '<table>';

	if ($_SESSION['Items'.$identifier]->Quotation=1){
		echo '<tr><th colspan="7">' . '<h3>Line No. '.($line+1). '</th></tr>';
		//$text = <<<EOD'<div>'. $TransferRowC['optiontext'].'</div>'EOD;
	//	$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n)#','<br/>',$TransferRowC['optiontext']);
		echo '<tr><th colspan="7">' . '<b>WE OFFER: </b><br />'.html_entity_decode($TransferRowC['optiontext']). '</th></tr>';
	} else {
		echo '<tr><th colspan="7">' . _('Order Summary') . '</th></tr>';
	}

	echo '<tr>
				<th>' .  _('Item Code')  . '</th>
				<th>' .  _('Item Description')  . '</th>
				<th>' .  _('Quantity')  . '</th>
				<th>' .  _('Unit')  . '</th>
				<th>' .  _('Price')  . '</th>
				<th>' .  _('Discount') .' %</th>
				<th>' .  _('Total')  . '</th>
			</tr>';

	$_SESSION['Items'.$identifier]->total = 0;
	$_SESSION['Items'.$identifier]->totalVolume = 0;
	$_SESSION['Items'.$identifier]->totalWeight = 0;
	$k = 0; //row colour counter

	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

	
		$LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
		$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		$DisplayPrice = locale_number_format($StockItem->Price,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		$DisplayQuantity = locale_number_format($StockItem->Quantity,$StockItem->DecimalPlaces);
		$DisplayDiscount = locale_number_format(($StockItem->DiscountPercent * 100),2);


		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
if(($line== $StockItem->orderlineno) AND ($option== $StockItem->lineoptionno) ) {
		echo '<td>' . $StockItem->StockID . '</td>
			<td title="' . $StockItem->LongDescription . '">' . $StockItem->ItemDescription . '</td>
			<td class="number">' . $DisplayQuantity . '</td>
			<td>' . $StockItem->Units . '</td>
			<td class="number">' . $DisplayPrice . '</td>
			<td class="number">' . $DisplayDiscount . '</td>
			<td class="number">' . $DisplayLineTotal . '</td>
		</tr>';

		$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $LineTotal;
		$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + ($StockItem->Quantity * $StockItem->Volume);
		$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + ($StockItem->Quantity * $StockItem->Weight);
	}
	
	}
	$DisplayTotal = number_format($_SESSION['Items'.$identifier]->total,2);
	echo '<tr class="EvenTableRows">
			<td colspan="6" class="number"><b>' .  _('TOTAL Excl Tax/Freight')  . '</b></td>
			<td class="number">' . $DisplayTotal . '</td>
		</tr>';
	
		echo'</table>';

	/*$DisplayVolume = locale_number_format($_SESSION['Items'.$identifier]->totalVolume,5);
	$DisplayWeight = locale_number_format($_SESSION['Items'.$identifier]->totalWeight,2);
	echo '<br />
		<table>
		<tr class="EvenTableRows">
			<td>' .  _('Total Weight') .':</td>
			<td class="number">' . $DisplayWeight . '</td>
			<td>' .  _('Total Volume') .':</td>
			<td class="number">' . $DisplayVolume . '</td>
		</tr>
		</table>';*/
	
} else {

/*Display the order without discount */

	echo '<div class="centre"><b>' . _('Order Summary') . '</b></div>
	<table class="selection">
	<tr>
		<th>' .  _('Item Description')  . '</th>
		<th>' .  _('Quantity')  . '</th>
		<th>' .  _('Unit')  . '</th>
		<th>' .  _('Price')  . '</th>
		<th>' .  _('Total')  . '</th>
	</tr>';

	$_SESSION['Items'.$identifier]->total = 0;
	$_SESSION['Items'.$identifier]->totalVolume = 0;
	$_SESSION['Items'.$identifier]->totalWeight = 0;
	$k=0; // row colour counter
	print_r($_SESSION['Items'.$identifier]->LineItems);
	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {
print_r($StockItem);
		$LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
		$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		$DisplayPrice = locale_number_format($StockItem->Price,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		$DisplayQuantity = locale_number_format($StockItem->Quantity,$StockItem->DecimalPlaces);

		if ($k==1){
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k=1;
		}
		echo '<td>' . $StockItem->ItemDescription  . '</td>
				<td class="number">' . $DisplayQuantity . '</td>
				<td>' . $StockItem->Units . '</td>
				<td class="number">' . $DisplayPrice . '</td>
				<td class="number">' . $DisplayLineTotal . '</font></td>
			</tr>';

		$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $LineTotal;
		$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + $StockItem->Quantity * $StockItem->Volume;
		$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + $StockItem->Quantity * $StockItem->Weight;

	}

	$DisplayTotal = locale_number_format($_SESSION['Items'.$identifier]->total,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);

	$DisplayVolume = locale_number_format($_SESSION['Items'.$identifier]->totalVolume,5);
	$DisplayWeight = locale_number_format($_SESSION['Items'.$identifier]->totalWeight,2);
	echo '<table class="selection">
			<tr>
				<td>' . _('Total Weight') . ':</td>
				<td>' . $DisplayWeight . '</td>
				<td>' . _('Total Volume') . ':</td>
				<td>' . $DisplayVolume . '</td>
			</tr>
		</table>';

}
	}
}
}
echo '<br />
	<table class="selection">
	<tr>
		<td>' .  _('Deliver To') .':</td>
		<td><input type="text" autofocus="autofocus" required="required" size="42" maxlength="40" name="DeliverTo" value="' .  stripslashes($_SESSION['Items' . $identifier]->DeliverTo) . '" title="' . _('Enter the name of the customer to deliver this order to') . '" /></td>
	</tr>';

echo '<tr>
	<td>' .  _('Deliver from the warehouse at') .':</td>
	<td><select name="Location">';

if ($_SESSION['Items'.$identifier]->Location=='' OR !isset($_SESSION['Items'.$identifier]->Location)) {
	$_SESSION['Items'.$identifier]->Location = $DefaultStockLocation;
}

$ErrMsg = _('The stock locations could not be retrieved');
$DbgMsg = _('SQL used to retrieve the stock locations was') . ':';
$StkLocsResult = DB_query("SELECT locationname,loccode
							FROM locations",$db, $ErrMsg, $DbgMsg);

while ($myrow=DB_fetch_array($StkLocsResult)){
	if ($_SESSION['Items'.$identifier]->Location==$myrow['loccode']){
		echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	} else {
		echo '<option value="'.$myrow['loccode'].'">' . $myrow['locationname'] . '</option>';
	}
}

echo '</select></td></tr>';

// Set the default date to earliest possible date if not set already
if (!isset($_SESSION['Items'.$identifier]->DeliveryDate)) {
	$_SESSION['Items'.$identifier]->DeliveryDate = Date($_SESSION['DefaultDateFormat'],$EarliestDispatch);
}
if (!isset($_SESSION['Items'.$identifier]->QuoteDate)) {
	$_SESSION['Items'.$identifier]->QuoteDate = Date($_SESSION['DefaultDateFormat'],$EarliestDispatch);
}
if (!isset($_SESSION['Items'.$identifier]->ConfirmedDate)) {
	$_SESSION['Items'.$identifier]->ConfirmedDate = Date($_SESSION['DefaultDateFormat'],$EarliestDispatch);
}

// The estimated Dispatch date or Delivery date for this order
echo '<tr>
		<td>' .  _('Estimated Delivery Date') .':</td>
		<td><input class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="text" size="15" maxlength="14" name="DeliveryDate" value="' . $_SESSION['Items'.$identifier]->DeliveryDate . '" title="' . _('Enter the estimated delivery date requested by the customer') . '"/></td>
	</tr>';
// The date when a quote was issued to the customer
echo '<tr>
		<td>' .  _('Quote Date') .':</td>
		<td><input class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="text" size="15" maxlength="14" name="QuoteDate" value="' . $_SESSION['Items'.$identifier]->QuoteDate . '" /></td>
	</tr>';
// The date when the customer confirmed their order
echo '<tr>
		<td>' .  _('Confirmed Order Date') .':</td>
		<td><input class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="text" size="15" maxlength="14" name="ConfirmedDate" value="' . $_SESSION['Items'.$identifier]->ConfirmedDate . '" /></td>
	</tr>
	<tr>
		<td>' .  _('Delivery Address 1 (Street)') . ':</td>
		<td><input type="text" size="42" maxlength="40" name="BrAdd1" value="' . $_SESSION['Items'.$identifier]->DelAdd1 . '" /></td>
	</tr>
	<tr>
		<td>' .  _('Delivery Address 2 (Street)') . ':</td>
		<td><input type="text" size="42" maxlength="40" name="BrAdd2" value="' . $_SESSION['Items'.$identifier]->DelAdd2 . '" /></td>
	</tr>
	<tr>
		<td>' .  _('Delivery Address 3 (Suburb/City)') . ':</td>
		<td><input type="text" size="42" maxlength="40" name="BrAdd3" value="' . $_SESSION['Items'.$identifier]->DelAdd3 . '" /></td>
	</tr>
	<tr>
		<td>' .  _('Delivery Address 4 (State/Province)') . ':</td>
		<td><input type="text" size="42" maxlength="40" name="BrAdd4" value="' . $_SESSION['Items'.$identifier]->DelAdd4 . '" /></td>
	</tr>
	<tr>
		<td>' .  _('Delivery Address 5 (Postal Code)') . ':</td>
		<td><input type="text" size="42" maxlength="40" name="BrAdd5" value="' . $_SESSION['Items'.$identifier]->DelAdd5 . '" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Country') . ':</td>
		<td><select name="BrAdd6">';
foreach ($CountriesArray as $CountryEntry => $CountryName){
	if (isset($_POST['BrAdd6']) AND (strtoupper($_POST['BrAdd6']) == strtoupper($CountryName))){
		echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
	}elseif (!isset($_POST['BrAdd6']) AND $CountryName == $_SESSION['Items'.$identifier]->DelAdd6) {
		echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
	} else {
		echo '<option value="' . $CountryName . '">' . $CountryName  . '</option>';
	}
}
echo '</select></td>
	</tr>';

echo'	<tr>
		<td>' .  _('Contact Phone Number') .':</td>
		<td><input type="tel" size="25" maxlength="25" name="PhoneNo" value="' . $_SESSION['Items'.$identifier]->PhoneNo . '" title="' . _('Enter the telephone number of the contact at the delivery address.') . '" /></td>
	</tr>
	<tr>
		<td>' . _('Contact Email') . ':</td>
		<td><input type="email" size="40" maxlength="38" name="Email" value="' . $_SESSION['Items'.$identifier]->Email . '" title="' . _('Enter the email address of the contact at the delivery address') . '" /></td>
	</tr>
	<tr>
		<td>' .  _('Customer Reference') .':</td>
		<td><input type="text" size="25" maxlength="25" name="CustRef" value="' . $_SESSION['Items'.$identifier]->CustRef . '" title="' . _('Enter the customer\'s purchase order reference relevant to this order') . '" /></td>
	</tr>
	<tr>
		<td>' .  _('Advance%') .':</td>
		<td><input type="number" size="25" maxlength="25" name="advance" value="' . $_SESSION['Items'.$identifier]->advance . '" /></td>
	</tr>
		<tr>
		<td>' .  _('Delivery%') .':</td>
		<td><input type="number" size="25" maxlength="25" name="delivery" value="' . $_SESSION['Items'.$identifier]->delivery . '"  /></td>
	</tr>
		<tr>
		<td>' .  _('Comminsioning%') .':</td>
		<td><input type="number" size="25" maxlength="25" name="commisioning" value="' . $_SESSION['Items'.$identifier]->delivery . '"  /></td>
	</tr>
		<tr>
		<td>' .  _('After%') .':</td>
		<td><input type="number" size="25" maxlength="25" name="after" value="' . $_SESSION['Items'.$identifier]->after . '"  /></td>
	</tr>
	<tr>
		<td>' .  _('GST Clause') .':</td>
		<td><input type="text" size="150"  maxlength="150" name="GST" value="' . $_SESSION['Items'.$identifier]->gst . '"  /></td>
	</tr>

	</td></tr>';
	echo' <tr>
	<td style="font-weight:bold; font-size:18px;" colspan = "3">';

echo	'
		<tr><td>
			After Days <select name="afterdays">';
			
			if (!isset($_POST['afterdays'])){
	$_POST['salescasevalue'] = $_SESSION['SalesCase'.$_POST['salescaseref']]->salescasevalue;
}
if ($_SESSION['Items'.$identifier]->afterdays==''){
	echo '<option selected="selected" value="">' . _('Select Credit Days') . '</option>
          <option value="15">' . _('15 Days') . '</option>
          <option value="30">' . _('30 Days') . '</option>
          <option value="45">' . _('45 Days') . '</option>
		  <option value="60">' . _('60 Days') . '</option>
          
		  ';
		  
} else if ($_SESSION['Items'.$identifier]->afterdays=='15'){
	echo '<option value="">' . _('Select Credit Days') . '</option>
          <option  selected="selected" value="15">' . _('15 Days') . '</option>
          <option value="30">' . _('30 Days') . '</option>
          <option value="45">' . _('45 Days') . '</option>
		  <option value="60">' . _('60 Days') . '</option>
          
		  ';
		  }
 else if ($_SESSION['Items'.$identifier]->afterdays=='30'){
	echo '<option value="">' . _('Select Credit Days') . '</option>
          <option  value="15">' . _('15 Days') . '</option>
          <option selected = "selected" value="30">' . _('30 Days') . '</option>
          <option value="45">' . _('45 Days') . '</option>
		  <option value="60">' . _('60 Days') . '</option>
          
		  ';
		  } 
		else if ($_SESSION['Items'.$identifier]->afterdays=='45'){
	echo '<option value="">' . _('Select Credit Days') . '</option>
          <option  value="15">' . _('15 Days') . '</option>
          <option value="30">' . _('30 Days') . '</option>
          <option selected = "selected" value="45">' . _('45 Days') . '</option>
		  <option value="60">' . _('60 Days') . '</option>
          
		  ';
		
		  }
	else if ($_SESSION['Items'.$identifier]->afterdays=='60'){
	echo '<option value="">' . _('Select Credit Days') . '</option>
          <option  value="15">' . _('15 Days') . '</option>
          <option  value="30">' . _('30 Days') . '</option>
          <option  value="45">' . _('45 Days') . '</option>
		  <option selected = "selected" value="60">' . _('60 Days') . '</option>
          
		  ';
		 }
			
			
			
			
			
			echo'</select></td>
			<tr><td colspan="3"><hr></td></tr>
	
	';
	
	
	
	
	echo '<tr>
		<td>' .  _('Comments') .':</td>
		<td><textarea name="Comments" cols="31" rows="5">' . $_SESSION['Items'.$identifier]->Comments  . '</textarea></td>
	</tr>';

	if ($CustomerLogin  == 1){
		echo '<input type="hidden" name="SalesPerson" value="' . $_SESSION['Items'.$identifier]->SalesPerson . '" />
			<input type="hidden" name="DeliverBlind" value="1" />
			<input type="hidden" name="FreightCost" value="0" />
			<input type="hidden" name="ShipVia" value="' . $_SESSION['Items'.$identifier]->ShipVia . '" />
			<input type="hidden" name="Quotation" value="1" />';
	} else {
		/*
		echo '<tr>
				<td>' . _('Sales person'). ':</td>
				<td><select name="SalesPerson">';
		$SalesPeopleResult = DB_query("SELECT salesmancode, salesmanname FROM salesman WHERE current=1",$db);
		if (!isset($_POST['SalesPerson']) AND $_SESSION['SalesmanLogin']!=NULL ){
			$_SESSION['Items'.$identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		}

		while ($SalesPersonRow = DB_fetch_array($SalesPeopleResult)){
			if ($SalesPersonRow['salesmancode']==$_SESSION['Items'.$identifier]->SalesPerson){
				echo '<option selected="selected" value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			} else {
				echo '<option value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			}
		}

		echo '</select></td>
			</tr>';*/

		/* This field will control whether or not to display the company logo and
		address on the packlist */

		echo '<tr><td>' . _('Packlist Type') . ':</td>
				<td><select name="DeliverBlind">';

		if ($_SESSION['Items'.$identifier]->DeliverBlind ==2){
			echo '<option value="1">' . _('Show Company Details/Logo') . '</option>';
			echo '<option selected="selected" value="2">' . _('Hide Company Details/Logo') . '</option>';
		} else {
			echo '<option selected="selected" value="1">' . _('Show Company Details/Logo') . '</option>';
			echo '<option value="2">' . _('Hide Company Details/Logo') . '</option>';
		}
		echo '</select></td></tr>';

		if (isset($_SESSION['PrintedPackingSlip']) AND $_SESSION['PrintedPackingSlip']==1){

			echo '<tr>
				<td>' .  _('Reprint order confirmation note') .':</td>
				<td><select name="ReprintPackingSlip">';
			echo '<option value="0">' . _('Yes') . '</option>';
			echo '<option selected="selected" value="1">' . _('No') . '</option>';
			echo '</select>	'. _('Last printed') .': ' . ConvertSQLDate($_SESSION['DatePackingSlipPrinted']) . '</td></tr>';
		} else {
			echo '<tr><td><input type="hidden" name="ReprintPackingSlip" value="0" /></td></tr>';
		}

		echo '<tr>
				<td>' .  _('Charge Freight Cost ex tax') .':</td>
				<td><input type="text" class="number" size="10" maxlength="12" name="FreightCost" value="' . $_SESSION['Items'.$identifier]->FreightCost . '" /></td>';

		if ($_SESSION['DoFreightCalc']==true){
			echo '<td><input type="submit" name="Update" value="' . _('Recalc Freight Cost') . '" /></td>';
		}
		echo '</tr>';

		if ((!isset($_POST['ShipVia']) OR $_POST['ShipVia']=='') AND isset($_SESSION['Items'.$identifier]->ShipVia)){
			$_POST['ShipVia'] = $_SESSION['Items'.$identifier]->ShipVia;
		}

		echo '<tr>
				<td>' .  _('Freight/Shipper Method') .':</td>
				<td><select name="ShipVia">';

		$ErrMsg = _('The shipper details could not be retrieved');
		$DbgMsg = _('SQL used to retrieve the shipper details was') . ':';

		$sql = "SELECT shipper_id, shippername FROM shippers";
		$ShipperResults = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		while ($myrow=DB_fetch_array($ShipperResults)){
			if ($myrow['shipper_id']==$_POST['ShipVia']){
					echo '<option selected="selected" value="' . $myrow['shipper_id'] . '">' . $myrow['shippername'] . '</option>';
			}else {
				echo '<option value="' . $myrow['shipper_id'] . '">' . $myrow['shippername'] . '</option>';
			}
		}

		echo '</select></td></tr>';

		
		echo '<input type="hidden" name = "Quotation" value = "1">';
	/*	<tr><td>' .  _('Quotation Only') .':</td>
				<td><select name="Quotation">';
		if ($_SESSION['Items'.$identifier]->Quotation==1){
			echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
			echo '<option value="0">' . _('No') . '</option>';
		} else {
			echo '<option value="1">' . _('Yes') . '</option>';
			echo '<option selected="selected" value="0">' . _('No') . '</option>';
		}
		echo '</select></td></tr>';*/
	} //end if it is NOT a CustomerLogin

echo '</table>';

echo '<br /><div class="centre"><input type="submit" name="BackToLineDetails" value="' . _('Modify Order Lines') . '" /><br />';

if ($_SESSION['ExistingOrder'.$identifier]==0){
	echo '<br /><br /><input type="submit" name="ProcessOrder" value="' . _('Place Order') . '" />';
	echo '<br /><br /><input type="submit" name="MakeRecurringOrder" value="' . _('Create Recurring Order') . '" />';
} else {
	echo '<br /><input type="submit" name="ProcessOrder" value="' . _('Commit Order Changes') . '" />';
}

echo '</div>
      </div>
      </form>';
include('includes/footer.inc');
?>
