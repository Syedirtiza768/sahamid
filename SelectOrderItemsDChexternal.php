


<?php
/* $Id: SelectOrderItemsDCh.php 6549 2014-01-24 20:32:31Z daintree $*/

include('includes/DefineDChCartClass.php');

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/

include('includes/session.inc');
unset($_SESSION[$nm]);
$scrollx = 0;

$scrolly = 0;

if(!empty($_POST['scrollx'])) {

$scrollx = $_POST['scrollx'];

}

if(!empty($_POST['scrolly'])) {

$scrolly = $_POST['scrolly'];

}
echo'
<script>
window.scrollTo(<?php echo "$scrollx" ?>, <?php echo "$scrolly" ?>);
</script>
';
if (isset($_GET['salescaseref']))
{
	$_SESSION['salescaseref']=$_GET['salescaseref'];
}


if (isset($_GET['selectedcustomer']))
{
	$_SESSION['SelectedCustomer']=$_GET['selectedcustomer'];
	$SelectedCustomer = $_GET['selectedcustomer'];
}
 
if (isset($_GET['BranchCode']))
{
	$_SESSION['BranchCode']=$_GET['BranchCode'];
	$SelectedBranch=$_GET['BranchCode'];
}

if (isset($_GET['DebtorNo']))
{
	$_SESSION['DebtorNo']=$_GET['DebtorNo'];
	
}





if (isset($_GET['ModifyOrderNumber'])) {
	$Title = _('Modifying Order') . ' ' . $_GET['ModifyOrderNumber'];
} else {
	$Title = _('Select Order Items');
}
/* webERP manual links before header.inc */
$ViewTopic= 'dcs';
$BookMark = 'dcEntry';

include('includes/header.inc');
?>


<?php
//print_r($_POST);




?>

 <!--     <script src="tinymce/tinymce.min.js"></script>	-->
  
   

<?php
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['QuickEntry'])){
	unset($_POST['PartSearch']);
}

if (isset($_POST['SelectingOrderItems'])){
	foreach ($_POST as $FormVariable => $Quantity) {
		if (mb_strpos($FormVariable,'OrderQty')!==false) {
			$NewItemArray[$_POST['StockID' . mb_substr($FormVariable,8)]] = filter_number_format($Quantity);
		}
	}
}

if (isset($_GET['NewItem'])){
	$NewItem = trim($_GET['NewItem']);
}

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}
$_SESSION['identifier']=$identifier;
//print_r($_SESSION['Items'.$identifier]->externallines);
if (isset($_GET['NewOrder'])){
  /*New order entry - clear any existing order details from the Items object and initiate a newy*/
	 if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		$_SESSION['Items'.$identifier]->ItemsOrdered=0;
		unset ($_SESSION['Items'.$identifier]);
		
	}

	$_SESSION['ExistingOrder' .$identifier]=$identifier;
	$_SESSION['Items'.$identifier] = new cart;
	
		$sql='DELETE FROM dclines where orderno = 0
	
	';
	DB_query($sql,$db);
		$sql='DELETE FROM dcoptions where orderno = 0
	
	';
	DB_query($sql,$db);
	
		$sql='INSERT INTO dclines (orderno,lineno) VALUES ('.$identifier.',
	0
	
	)';
	DB_query($sql,$db);
	
	$sql='INSERT into dcoptions (orderno,lineno,optionno) VALUES ('.$identifier.',
	0,
	0
	
	)';
	DB_query($sql,$db);
	
	// write a query here and fetch the results of newly added line
	
	$newlinesql='SELECT * FROM dclines where orderno = "'.$_SESSION['ExistingOrder' .$identifier].'"
	AND lineno=0
	';
	$newlineresult = DB_query($newlinesql,$db);
	$newlinerow = DB_fetch_array($newlineresult);
		$_SESSION['Items'.$identifier]->externallines[0] = new externalline(
		$newlinerow['lineindex'],
		$newlinerow['orderno'],
		0,
		$newlinerow['clientrequirements']
		
		);
		$newoptionsql='SELECT * FROM dcoptions where orderno = "'.$_SESSION['ExistingOrder' .$identifier].'"
	AND lineno=0 AND optionno = 0
	';
	$newoptionresult = DB_query($newoptionsql,$db);
	$newoptionrow = DB_fetch_array($newoptionresult);
		$_SESSION['Items'.$identifier]->externallines[0]->lineoptions[0] = new lineoption(
		$newoptionrow['optionindex'],
		$newoptionrow['orderno'],
		$newoptionrow['lineno'],
		$newoptionrow['optionno'],
		$newoptionrow['optiontext'],
		$newoptionrow['quantity'],
		$newoptionrow['stockstatus']
		
		);

	

	if (isset($CustomerLogin)){ //its a customer logon
		$_SESSION['Items'.$identifier]->DebtorNo=$_SESSION['CustomerID'];
		$_SESSION['Items'.$identifier]->BranchCode=$_SESSION['UserBranch'];
		$SelectedCustomer = $_SESSION['CustomerID'];
		$SelectedBranch = $_SESSION['UserBranch'];
		$_SESSION['RequireCustomerSelection'] = 0;
	} else {
		$_SESSION['Items'.$identifier]->DebtorNo=$_GET['DebtorNo'];
		$_SESSION['Items'.$identifier]->BranchCode=$_GET['BranchCode'];
		$_SESSION['RequireCustomerSelection'] = 1;
	}

}

if ( isset($_GET['ModifyOrderNumber'])
	AND $_GET['ModifyOrderNumber']!=''){

/* The delivery check screen is where the details of the order are either updated or inserted depending on the value of ExistingOrder */

	if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		unset ($_SESSION['Items'.$identifier]);
	}
	$_SESSION['ExistingOrder'.$identifier]=$_GET['ModifyOrderNumber'];
	$_SESSION['RequireCustomerSelection'] = 0;
	$_SESSION['Items'.$identifier] = new cart;

/*read in all the guff from the selected order into the Items cart  */

	$OrderHeaderSQL = "SELECT dcs.debtorno,
			 				  debtorsmaster.name,
							  dcs.branchcode,
							  dcs.customerref,
							  dcs.comments,
							  dcs.orddate,
							  dcs.ordertype,
							  salestypes.sales_type,
							  dcs.shipvia,
							  dcs.deliverto,
							  dcs.deladd1,
							  dcs.deladd2,
							  dcs.deladd3,
							  dcs.deladd4,
							  dcs.deladd5,
							  dcs.deladd6,
							  dcs.contactphone,
							  dcs.contactemail,
							  dcs.salesperson,
							  dcs.freightcost,
							  dcs.advance,
							  dcs.delivery,
							  dcs.commisioning,
							  dcs.after,
							  dcs.afterdays,
							  dcs.deliverydate,
							  debtorsmaster.currcode,
							  currencies.decimalplaces,
							  paymentterms.terms,
							  dcs.fromstkloc,
							  dcs.printedpackingslip,
							  dcs.datepackingslipprinted,
							  dcs.quotation,
							  dcs.deliverblind,
							  debtorsmaster.customerpoline,
							  locations.locationname,
							  custbranch.estdeliverydays,
							  custbranch.salesman
						FROM dcs
						INNER JOIN debtorsmaster
						ON dcs.debtorno = debtorsmaster.debtorno
						INNER JOIN salestypes
						ON dcs.ordertype=salestypes.typeabbrev
						INNER JOIN custbranch
						ON dcs.debtorno = custbranch.debtorno
						AND dcs.branchcode = custbranch.branchcode
						INNER JOIN paymentterms
						ON debtorsmaster.paymentterms=paymentterms.termsindicator
						INNER JOIN locations
						ON locations.loccode=dcs.fromstkloc
						INNER JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
						WHERE dcs.orderno = '" . $_GET['ModifyOrderNumber'] . "'";

	$ErrMsg =  _('The order cannot be retrieved because');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg);

	if (DB_num_rows($GetOrdHdrResult)==1) {

		$myrow = DB_fetch_array($GetOrdHdrResult);
		if ($_SESSION['SalesmanLogin']!='' AND $_SESSION['SalesmanLogin']!=$myrow['salesman']){
			prnMsg(_('Your account is set up to see only a specific salespersons orders. You are not authorised to modify this order'),'error');
			include('includes/footer.inc');
			exit;
		}
		$_SESSION['Items'.$identifier]->OrderNo = $_GET['ModifyOrderNumber'];
		$_SESSION['Items'.$identifier]->DebtorNo = $myrow['debtorno'];
/*CustomerID defined in header.inc */
		$_SESSION['Items'.$identifier]->Branch = $myrow['branchcode'];
		$_SESSION['Items'.$identifier]->CustomerName = $myrow['name'];
		$_SESSION['Items'.$identifier]->CustRef = $myrow['customerref'];
		$_SESSION['Items'.$identifier]->Comments = stripcslashes($myrow['comments']);
		$_SESSION['Items'.$identifier]->PaymentTerms =$myrow['terms'];
		$_SESSION['Items'.$identifier]->DefaultSalesType =$myrow['ordertype'];
		$_SESSION['Items'.$identifier]->SalesTypeName =$myrow['sales_type'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];
		$_SESSION['Items'.$identifier]->ShipVia = $myrow['shipvia'];
		$BestShipper = $myrow['shipvia'];
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['deliverto'];
		$_SESSION['Items'.$identifier]->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['deladd1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['deladd2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['deladd3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['deladd4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['deladd5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['deladd6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['contactphone'];
		$_SESSION['Items'.$identifier]->Email = $myrow['contactemail'];
		$_SESSION['Items'.$identifier]->SalesPerson = $myrow['salesperson'];
		$_SESSION['Items'.$identifier]->Location = $myrow['fromstkloc'];
		$_SESSION['Items'.$identifier]->LocationName = $myrow['locationname'];
		$_SESSION['Items'.$identifier]->Quotation = $myrow['quotation'];
		
		$_SESSION['Items'.$identifier]->FreightCost = $myrow['freightcost'];
		$_SESSION['Items'.$identifier]->advance = $myrow['advance'];
		$_SESSION['Items'.$identifier]->delivery = $myrow['delivery'];
		$_SESSION['Items'.$identifier]->commisioning = $myrow['commisioning'];
		$_SESSION['Items'.$identifier]->after = $myrow['after'];
		$_SESSION['Items'.$identifier]->afterdays = $myrow['afterdays'];
		
		$_SESSION['Items'.$identifier]->Orig_OrderDate = $myrow['orddate'];
		$_SESSION['PrintedPackingSlip'] = $myrow['printedpackingslip'];
		$_SESSION['DatePackingSlipPrinted'] = $myrow['datepackingslipprinted'];
		$_SESSION['Items'.$identifier]->DeliverBlind = $myrow['deliverblind'];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];

		//Get The exchange rate used for GPPercent calculations on adding or amending items
		if ($_SESSION['Items'.$identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']){
			$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items'.$identifier]->DefaultCurrency . "'",$db);
			if (DB_num_rows($ExRateResult)>0){
				$ExRateRow = DB_fetch_row($ExRateResult);
				$ExRate = $ExRateRow[0];
			} else {
				$ExRate =1;
			}
		} else {
			$ExRate = 1;
		}

/*need to look up customer name from debtors master then populate the line items array with the sales order details records */

			$LineItemsSQL = "SELECT dcdetails.internalitemno,
									dcdetails.orderlineno,
									dcdetails.lineoptionno,
									dcdetails.optionitemno,
									dcdetails.stkcode,
									stockmaster.description,
									stockmaster.longdescription,
									stockmaster.materialcost,
									stockmaster.volume,
									stockmaster.grossweight,
									stockmaster.units,
									stockmaster.serialised,
									stockmaster.nextserialno,
									stockmaster.eoq,
									dcdetails.unitprice,
									dcdetails.quantity,
									dcdetails.discountpercent,
									dcdetails.actualdispatchdate,
									dcdetails.qtyinvoiced,
									dcdetails.narrative,
									dcdetails.itemdue,
									dcdetails.poline,
									locstock.quantity as qohatloc,
									stockmaster.mbflag,
									stockmaster.discountcategory,
									stockmaster.decimalplaces,
									stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
									dcdetails.completed
								FROM dcdetails INNER JOIN stockmaster
								ON dcdetails.stkcode = stockmaster.stockid
								INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
								WHERE  locstock.loccode = '" . $myrow['fromstkloc'] . "'
								AND dcdetails.orderno ='" . $_GET['ModifyOrderNumber'] . "'
								ORDER BY dcdetails.orderlineno";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$LineItemsResult = DB_query($LineItemsSQL,$db,$ErrMsg);
		if (DB_num_rows($LineItemsResult)>0) {

			while ($myrow=DB_fetch_array($LineItemsResult)) {
					if ($myrow['completed']==0){
						//print_r($myrow);
						$sqllistprice='select materialcost from stockmaster where stockid = "'.$myrow['stkcode'].'"';
				$sqllistpriceresult=DB_query($sqllistprice,$db);
				$sqllistpriceresultrow = DB_fetch_array($sqllistpriceresult);
						$_SESSION['Items'.$identifier]->add_to_cart($myrow['stkcode'],
																	$myrow['quantity'],
																	$myrow['description'],
																	$myrow['longdescription'],
																	$myrow['materialcost'],
																	$myrow['discountpercent'],
																	$myrow['units'],
																	$myrow['volume'],
																	$myrow['grossweight'],
																	$myrow['qohatloc'],
																	$myrow['mbflag'],
																	$myrow['actualdispatchdate'],
																	$myrow['qtyinvoiced'],
																	$myrow['discountcategory'],
																	0,	/*Controlled*/
																	$myrow['serialised'],
																	$myrow['decimalplaces'],
																	$myrow['narrative'],
																	'No', /* Update DB */
																	$myrow['internalitemno'],
																	$myrow['orderlineno'],
																	$myrow['lineoptionno'],
																	$myrow['optionitemno'],
																	
																	0,
																	'',
																	ConvertSQLDate($myrow['itemdue']),
																	$myrow['poline'],
																	$myrow['standardcost'],
																	$myrow['eoq'],
																	$myrow['nextserialno'],
																	$ExRate,
																	$identifier );

				/*Just populating with existing order - no DBUpdates */
					}
					
					$LastLineNo = $myrow['internalitemno'];
			} /* line items from sales order details */
			 $_SESSION['Items'.$identifier]->LineCounter = $LastLineNo+1;
		} //end of checks on returned data set
		
		
	$externallinessql = "SELECT *
								FROM dclines 
								WHERE  dclines.orderno ='" . $_GET['ModifyOrderNumber'] . "'
								ORDER BY dclines.lineno";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$externallinessqlresult = DB_query($externallinessql,$db,$ErrMsg);
	if (DB_num_rows($externallinessqlresult)>0) {
//($externallinessqlresult);
	//Adding line options
			while ($myrow=DB_fetch_array($externallinessqlresult)) {
				//	print_r($myrow);
						$_SESSION['Items'.$identifier]->externallines[$myrow['lineno']] = new externalline(
						$myrow['lineindex'],
		$myrow['orderno'],
		$myrow['lineno'],
		$myrow['clientrequirements']
						
						
						);
						
		} //
		}
	}

		$externallinessql = "SELECT *
								FROM dclines 
								WHERE  dclines.orderno ='" . $_GET['ModifyOrderNumber'] . "'
								ORDER BY dclines.lineno";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$externallinessqlresult = DB_query($externallinessql,$db,$ErrMsg);
	
		if (DB_num_rows($externallinessqlresult)>0) {

			while ($myrow=DB_fetch_array($externallinessqlresult)) {
	$lineoptionsssql = "SELECT *
								FROM dcoptions 
								WHERE  dcoptions.orderno ='" . $_GET['ModifyOrderNumber'] . "'
								AND 	dcoptions.lineno ='" . $myrow['lineno'] . "'
								ORDER BY dcoptions.lineno";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$lineoptionsssqlresult = DB_query($lineoptionsssql,$db,$ErrMsg);
		
	//	print_r($lineoptionsssqlresult);
		if (DB_num_rows($lineoptionsssqlresult)>0) {

			while ($myrow=DB_fetch_array($lineoptionsssqlresult)) {
						
						$_SESSION['Items'.$identifier]->externallines[$myrow['lineno']]->lineoptions[$myrow['optionno']] = new lineoption(
						$myrow['optionindex'],
						$myrow['orderno'],
						$myrow['lineno'],
						$myrow['optionno'],
						$myrow['optiontext'],
						$myrow['quantity'],
						$myrow['stockstatus']
						);
						
			//	print_r($_SESSION['Items'.$identifier]->externallines[$myrow['lineno']]->lineoptions[$myrow['optionno']]);
			
			}
		
		}
			}
			
}
	//print_r($_SESSION['Items'.$identifier]);
	}
if (isset($SelectedCustomer)) {

	$_SESSION['Items'.$identifier]->DebtorNo = trim($SelectedCustomer);
	$_SESSION['Items'.$identifier]->Branch = trim($SelectedBranch);

	// Now check to ensure this account is not on hold */
	$sql = "SELECT debtorsmaster.name,
					holdreasons.dissallowinvoices,
					debtorsmaster.salestype,
					salestypes.sales_type,
					debtorsmaster.currcode,
					debtorsmaster.customerpoline,
					paymentterms.terms,
					currencies.decimalplaces
			FROM debtorsmaster INNER JOIN holdreasons
			ON debtorsmaster.holdreason=holdreasons.reasoncode
			INNER JOIN salestypes
			ON debtorsmaster.salestype=salestypes.typeabbrev
			INNER JOIN paymentterms
			ON debtorsmaster.paymentterms=paymentterms.termsindicator
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtorsmaster.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo. "'";

	$ErrMsg = _('The details of the customer selected') . ': ' .  $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was') . ':';
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

	$myrow = DB_fetch_array($result);
	if ($myrow[1] != 1){
		if ($myrow[1]==2){
			prnMsg(_('The') . ' ' . htmlspecialchars($myrow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _('account is currently flagged as an account that needs to be watched. Please contact the credit control personnel to discuss'),'warn');
		}

		$_SESSION['RequireCustomerSelection']=0;
		$_SESSION['Items'.$identifier]->CustomerName = $myrow['name'];

# the sales type determines the price list to be used by default the customer of the user is
# defaulted from the entry of the userid and password.

		$_SESSION['Items'.$identifier]->DefaultSalesType = $myrow['salestype'];
		$_SESSION['Items'.$identifier]->SalesTypeName = $myrow['sales_type'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];
		$_SESSION['Items'.$identifier]->PaymentTerms = $myrow['terms'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];

# the branch was also selected from the customer selection so default the delivery details from the customer branches table CustBranch. The order process will ask for branch details later anyway

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
						locations.locationname,
						custbranch.salesman
					FROM custbranch
					INNER JOIN locations
					ON custbranch.defaultlocation=locations.loccode
				WHERE custbranch.branchcode='" . $SelectedBranch . "'
				AND custbranch.debtorno = '" . $SelectedCustomer . "'";

		$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('SQL used to retrieve the branch details was') . ':';
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result)==0){

			prnMsg(_('The branch details for branch code') . ': ' . $_SESSION['Items'.$identifier]->Branch . ' ' . _('against customer code') . ': ' . $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('could not be retrieved') . '. ' . _('Check the set up of the customer and branch'),'error');

			if ($debug==1){
				prnMsg( _('The SQL that failed to get the branch details was') . ':<br />' . $sql . 'warning');
			}
			include('includes/footer.inc');
			exit;
		}
		// add echo
		echo '<br />';
		$myrow = DB_fetch_array($result);
		if ($_SESSION['SalesmanLogin']!=NULL AND $_SESSION['SalesmanLogin']!=$myrow['salesman']){
			prnMsg(_('Your login is only set up for a particular salesperson. This customer has a different salesperson.'),'error');
			include('includes/footer.inc');
			exit;
		}
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['brname'];
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['braddress1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['braddress2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['braddress3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['braddress4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['braddress5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['braddress6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['phoneno'];
		$_SESSION['Items'.$identifier]->Email = $myrow['email'];
		$_SESSION['Items'.$identifier]->Location = $myrow['defaultlocation'];
		$_SESSION['Items'.$identifier]->ShipVia = $myrow['defaultshipvia'];
		$_SESSION['Items'.$identifier]->DeliverBlind = $myrow['deliverblind'];
		$_SESSION['Items'.$identifier]->SpecialInstructions = $myrow['specialinstructions'];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];
		$_SESSION['Items'.$identifier]->LocationName = $myrow['locationname'];
		if ($_SESSION['SalesmanLogin']!= NULL AND $_SESSION['SalesmanLogin']!=''){
			$_SESSION['Items'.$identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		} else {
			$_SESSION['Items'.$identifier]->SalesPerson = $myrow['salesman'];
		}
		if ($_SESSION['Items'.$identifier]->SpecialInstructions)
		  prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');

		if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn and 2 for prohibit sales */

			$_SESSION['Items'.$identifier]->CreditAvailable = GetCreditAvailable($_SESSION['Items'.$identifier]->DebtorNo,$db);

			if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
				prnMsg(_('The') . ' ' . htmlspecialchars($myrow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _('account is currently at or over their credit limit'),'warn');
			} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
				prnMsg(_('No more orders can be placed by') . ' ' . htmlspecialchars($myrow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _(' their account is currently at or over their credit limit'),'warn');
				include('includes/footer.inc');
				exit;
			}
		}

	} else {
		prnMsg(_('The') . ' ' . htmlspecialchars($myrow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _('account is currently on hold please contact the credit control personnel to discuss'),'warn');
	}

} elseif (!$_SESSION['Items'.$identifier]->DefaultSalesType
			OR $_SESSION['Items'.$identifier]->DefaultSalesType=='')	{

#Possible that the check to ensure this account is not on hold has not been done
#if the customer is placing own order, if this is the case then
#DefaultSalesType will not have been set as above

	$sql = "SELECT debtorsmaster.name,
					holdreasons.dissallowinvoices,
					debtorsmaster.salestype,
					debtorsmaster.currcode,
					currencies.decimalplaces,
					debtorsmaster.customerpoline
			FROM debtorsmaster
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason=holdreasons.reasoncode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtorsmaster.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

	$ErrMsg = _('The details for the customer selected') . ': ' .$_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('SQL used to retrieve the customer details was') . ':<br />' . $sql;
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

	$myrow = DB_fetch_array($result);
	if ($myrow[1] == 0){

		$_SESSION['Items'.$identifier]->CustomerName = $myrow[0];

# the sales type determines the price list to be used by default the customer of the user is
# defaulted from the entry of the userid and password.

		$_SESSION['Items'.$identifier]->DefaultSalesType = $myrow['salestype'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];
		$_SESSION['Items'.$identifier]->Branch = $_SESSION['UserBranch'];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];

	// the branch would be set in the user data so default delivery details as necessary. However,
	// the order process will ask for branch details later anyway

		$sql = "SELECT custbranch.brname,
						custbranch.branchcode,
						custbranch.braddress1,
						custbranch.braddress2,
						custbranch.braddress3,
						custbranch.braddress4,
						custbranch.braddress5,
						custbranch.braddress6,
						custbranch.phoneno,
						custbranch.email,
						custbranch.defaultlocation,
						custbranch.deliverblind,
						custbranch.estdeliverydays,
						locations.locationname,
						custbranch.salesman
				FROM custbranch INNER JOIN locations
				ON custbranch.defaultlocation=locations.loccode
				WHERE custbranch.branchcode='" . $SelectedBranch . "'
				AND custbranch.debtorno = '" . $SelectedCustomer . "'";

		$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('SQL used to retrieve the branch details was');
		$result =DB_query($sql,$db,$ErrMsg, $DbgMsg);

		$myrow = DB_fetch_array($result);
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['brname'];
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['braddress1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['braddress2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['braddress3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['braddress4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['braddress5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['braddress6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['phoneno'];
		$_SESSION['Items'.$identifier]->Email = $myrow['email'];
		$_SESSION['Items'.$identifier]->Location = $myrow['defaultlocation'];
		$_SESSION['Items'.$identifier]->DeliverBlind = $myrow['deliverblind'];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];
		$_SESSION['Items'.$identifier]->LocationName = $myrow['locationname'];
		if ($_SESSION['SalesmanLogin']!= NULL AND $_SESSION['SalesmanLogin']!=''){
			$_SESSION['Items'.$identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		} else {
			$_SESSION['Items'.$identifier]->SalesPerson = $myrow['salesman'];
		}
	} else {
		prnMsg(_('Sorry, your account has been put on hold for some reason, please contact the credit control personnel.'),'warn');
		include('includes/footer.inc');
		exit;
	}
}

if ($_SESSION['RequireCustomerSelection'] ==1
	OR !isset($_SESSION['Items'.$identifier]->DebtorNo)
	OR $_SESSION['Items'.$identifier]->DebtorNo=='') {

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' .
	' ' . _('Enter an Order or Quotation') . ' : ' . _('Search for the Customer Branch.') . '</p>';
	echo '<div class="page_help_text">' . _('Orders/Quotations are placed against the Customer Branch. A Customer may have several Branches.') . '</div>';
	
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier . '" method="post">
		 <div>
			 <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			 <table cellpadding="3" class="selection">
				<tr>
				<td>' . _('Part of the Customer Branch Name') . ':</td>
				<td><input tabindex="1" type="text" autofocus="autofocus" name="CustKeywords" size="20" maxlength="25" title="' . _('Enter a text extract of the customer\'s name, then click Search Now to find customers matching the entered name') . '" /></td>
				<td><b>' . _('OR') . '</b></td>
				<td>' . _('Part of the Customer Branch Code') . ':</td>
				<td><input tabindex="2" type="text" name="CustCode" size="15" maxlength="18" title="' . _('Enter a part of a customer code that you wish to search for then click the Search Now button to find matching customers') . '" /></td>
				<td><b>' . _('OR') . '</b></td>
				<td>' . _('Part of the Branch Phone Number') . ':</td>
				<td><input tabindex="3" type="text" name="CustPhone" size="15" maxlength="18" title="' . _('Enter a part of a customer\'s phone number that you wish to search for then click the Search Now button to find matching customers') . '"/></td>
				</tr>
				
			</table>
			
			<div class="centre">
				<input tabindex="4" type="submit" name="SearchCust" value="' . _('Search Now') . '" />
				<input tabindex="5" type="submit" name="reset" value="' .  _('Reset') . '" />
			</div>
		</div>';

	if (isset($result_CustSelect)) {

        echo '<div>
					<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
					<input type="hidden" name="JustSelectedACustomer" value="Yes" />
					<br />
					<table class="selection">';

		echo '<tr>
				<th class="ascending" >' . _('Customer') . '</th>
				<th class="ascending" >' . _('Branch') . '</th>
				<th class="ascending" >' . _('Contact') . '</th>
				<th>' . _('Phone') . '</th>
				<th>' . _('Fax') . '</th>
			</tr>';

		$j = 1;
		$k = 0; //row counter to determine background colour
		$LastCustomer='';
		while ($myrow=DB_fetch_array($result_CustSelect)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			echo '	<td>' . htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8', false) . '</td>
					<td><input tabindex="'.strval($j+5).'" type="submit" name="SubmitCustomerSelection' . $j .'" value="' . htmlspecialchars($myrow['brname'], ENT_QUOTES, 'UTF-8', false). '" />
					<input type="hidden" name="SelectedCustomer' . $j .'" value="'.$myrow['debtorno'].'" />
					<input type="hidden" name="SelectedBranch' . $j .'" value="'. $myrow['branchcode'].'" /></td>
					<td>' . $myrow['contactname'] . '</td>
					<td>' . $myrow['phoneno'] . '</td>
					<td>' . $myrow['faxno'] . '</td>
				</tr>';
			$LastCustomer=$myrow['name'];
			$j++;
//end of page full new headings if
		}
//end of while loop
        echo '</table>
			</div>';
	}//end if results to show
	echo '</form>';
	
//end if RequireCustomerSelection
} else { //dont require customer selection
// everything below here only do if a customer is selected

 	if (isset($_POST['CancelOrder'])) {
		$OK_to_delete=1;	//assume this in the first instance

		if($_SESSION['ExistingOrder' . $identifier]!=0) { //need to check that not already dispatched

			$sql = "SELECT qtyinvoiced
					FROM dcdetails
					WHERE orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'
					AND qtyinvoiced>0";

			$InvQties = DB_query($sql,$db);

			if (DB_num_rows($InvQties)>0){

				$OK_to_delete=0;

				prnMsg( _('There are lines on this order that have already been invoiced. Please delete only the lines on the order that are no longer required') . '<p>' . _('There is an option on confirming a dispatch/invoice to automatically cancel any balance on the order at the time of invoicing if you know the customer will not want the back order'),'warn');
			}
		}

		if ($OK_to_delete==1){
			if($_SESSION['ExistingOrder' . $identifier]!=0){

				$SQL = "DELETE FROM dcdetails WHERE dcdetails.orderno ='" . $_SESSION['ExistingOrder' . $identifier] . "'";
				$ErrMsg =_('The order detail lines could not be deleted because');
				$DelResult=DB_query($SQL,$db,$ErrMsg);

				$SQL = "DELETE FROM dcs WHERE dcs.orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'";
				$ErrMsg = _('The order header could not be deleted because');
				$DelResult=DB_query($SQL,$db,$ErrMsg);

				$_SESSION['ExistingOrder' . $identifier]=0;
			}

			unset($_SESSION['Items'.$identifier]->LineItems);
			$_SESSION['Items'.$identifier]->ItemsOrdered=0;
			unset($_SESSION['Items'.$identifier]);
			$_SESSION['Items'.$identifier] = new cart;

			if (in_array($_SESSION['PageSecurityArray']['ConfirmDispatch_Invoice.php'], $_SESSION['AllowedPageSecurityTokens'])){
				$_SESSION['RequireCustomerSelection'] = 1;
			} else {
				$_SESSION['RequireCustomerSelection'] = 0;
			}
			echo '<br /><br />';
			prnMsg(_('This sales order has been cancelled as requested'),'success');
			include('includes/footer.inc');
			exit;
		}
	} else { /*Not cancelling the order */

		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Order') . '" alt="" />' . ' ';

		if ($_SESSION['Items'.$identifier]->Quotation==1){
			echo _('<h3>DC for customer</h3>') . ' ';
		} else {
			echo _('Order for customer') . ' ';
		}

		echo ':<b> ' . $_SESSION['Items'.$identifier]->DebtorNo  . ' ' . _('Customer Name') . ': ' . htmlspecialchars($_SESSION['Items'.$identifier]->CustomerName, ENT_QUOTES, 'UTF-8', false);
	//	echo '</b></p><div class="page_help_text">' . '<b>' . _('Default Options (can be modified during order):') . '</b><br />' . _('Deliver To') . ':<b> ' . htmlspecialchars($_SESSION['Items'.$identifier]->DeliverTo, ENT_QUOTES, 'UTF-8', false);
		echo '</b>&nbsp;' . _('From Location') . ':<b> ' . $_SESSION['Items'.$identifier]->LocationName;
	//	echo '</b><br />' . _('Sales Type') . '/' . _('Price List') . ':<b> ' . $_SESSION['Items'.$identifier]->SalesTypeName;
	//	echo '</b><br />' . _('Terms') . ':<b> ' . $_SESSION['Items'.$identifier]->PaymentTerms;
		echo '</b></div>';
		echo '<table border="1"><tr><td><a href="SelectOrderItemsDCh.php?identifier='.$identifier .'" onclick="return confirm(\'' . _('Press OK only if description is saved') . '\');">Bill of Quantities</a></td><td><a style="color:green;" href="SelectOrderItemsDChexternal.php?identifier='.$identifier .'">Descriptions</a></td></tr></table>';
		echo '<h1>Add Descriptions</h1>';
	}
	$msg ='';
	if (isset($_POST['Search']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
		unset($_SESSION[$nm]);
		if(!empty($_POST['RawMaterialFlag'])){
			$RawMaterialSellable = " OR stockcategory.stocktype='M'";
		}else{
			$RawMaterialSellable = '';
		}

		if ($_POST['Keywords']!='' AND $_POST['StockCode']=='') {
			$msg='<div class="page_help_text">' . _('Order Item description has been used in search') . '.</div>';
		} elseif ($_POST['StockCode']!='' AND $_POST['Keywords']=='') {
			$msg='<div class="page_help_text">' . _('Stock Code has been used in search') . '.</div>';
		} elseif ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
			$msg='<div class="page_help_text">' . _('Stock Category has been used in search') . '.</div>';
		}
		if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND stockmaster.mbflag <>'G'
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
						
						AND stockmaster.discontinued=0
						ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid";
			}

		} elseif (mb_strlen($_POST['StockCode'])>0){

			$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
			$SearchString = '%' . $_POST['StockCode'] . '%';

			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND (stockmaster.mnfCode " . LIKE . " '%" . $SearchString . "%'
					or stockmaster.stockid " . LIKE . " '%" . $SearchString. "%')
						
						
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid";
			}

		} else {
			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid";
			  }
		}

		if (isset($_POST['Next'])) {
			$Offset = $_POST['NextList'];
		}
		if (isset($_POST['Previous'])) {
			$Offset = $_POST['PreviousList'];
		}
		if (!isset($Offset) OR $Offset < 0) {
			$Offset=0;
		}

		$SQL = $SQL . " LIMIT " . $_SESSION['DisplayRecordsMax'] . " OFFSET " . strval($_SESSION['DisplayRecordsMax'] * $Offset);

		$ErrMsg = _('There is a problem selecting the part records to display because');
		$DbgMsg = _('The SQL used to get the part selection was');

		$SearchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

		if (DB_num_rows($SearchResult)==0 ){
			prnMsg (_('There are no products available meeting the criteria specified'),'info');
		}
		if (DB_num_rows($SearchResult)==1){
			$myrow=DB_fetch_array($SearchResult);
			$NewItem = $myrow['stockid'];
			DB_data_seek($SearchResult,0);
		}
		if (DB_num_rows($SearchResult) < $_SESSION['DisplayRecordsMax']){
			$Offset=0;
		}
	} //end of if search

#Always do the stuff below if not looking for a customerid
?>

<?php
	echo '<form id="theForm" name="theForm" onsubmit="return saveScrollPositions(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier . '" id="SelectParts" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo'<input type="hidden" name="scrollx" id="scrollx" value="0" />';

echo'<input type="hidden" name="scrolly" id="scrolly" value="0" />';
//Get The exchange rate used for GPPercent calculations on adding or amending items
	if ($_SESSION['Items'.$identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']){
		$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items'.$identifier]->DefaultCurrency . "'",$db);
		if (DB_num_rows($ExRateResult)>0){
			$ExRateRow = DB_fetch_row($ExRateResult);
			$ExRate = $ExRateRow[0];
		} else {
			$ExRate =1;
		}
	} else {
		$ExRate = 1;
	}


	/*Process Quick Entry */
	/* If enter is pressed on the quick entry screen, the default button may be Recalculate */
	 if (isset($_POST['SelectingOrderItems'])
			OR isset($_POST['QuickEntry'])
			OR isset($_POST['Recalculate'])){
unset($_SESSION[$nm]);
		 /* get the item details from the database and hold them in the cart object */

		 /*Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
		$Discount = 0;
		$AlreadyWarnedAboutCredit = false;
		 $i=1;
		  while ($i<=$_SESSION['QuickEntries'] AND isset($_POST['part_' . $i]) AND $_POST['part_' . $i]!='') {
			$QuickEntryCode = 'part_' . $i;
			$QuickEntryQty = 'qty_' . $i;
			$QuickEntryPOLine = 'poline_' . $i;
			$QuickEntryItemDue = 'itemdue_' . $i;

			$i++;

			if (isset($_POST[$QuickEntryCode])) {
				$NewItem = mb_strtoupper($_POST[$QuickEntryCode]);
			}
			if (isset($_POST[$QuickEntryQty])) {
				$NewItemQty = filter_number_format($_POST[$QuickEntryQty]);
			}
			if (isset($_POST[$QuickEntryItemDue])) {
				$NewItemDue = $_POST[$QuickEntryItemDue];
			} else {
				$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
			}
			if (isset($_POST[$QuickEntryPOLine])) {
				$NewPOLine = $_POST[$QuickEntryPOLine];
			} else {
				$NewPOLine = 0;
			}

			if (!isset($NewItem)){
				unset($NewItem);
				break;	/* break out of the loop if nothing in the quick entry fields*/
			}

			if(!Is_Date($NewItemDue)) {
				prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $NewItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
				//Attempt to default the due date to something sensible?
				$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
			}
			/*Now figure out if the item is a kit set - the field MBFlag='K'*/
			$sql = "SELECT stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='". $NewItem ."'";

			$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
			$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
			$KitResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);


			if (DB_num_rows($KitResult)==0){
				prnMsg( _('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database and has not been added to the order'),'warn');
			} elseif ($myrow=DB_fetch_array($KitResult)){
				if ($myrow['mbflag']=='K'){	/*It is a kit set item */
					$sql = "SELECT bom.component,
							bom.quantity
							FROM bom
							WHERE bom.parent='" . $NewItem . "'
							AND bom.effectiveto > '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

					$ErrMsg =  _('Could not retrieve kitset components from the database because') . ' ';
					$KitResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

					$ParentQty = $NewItemQty;
					while ($KitParts = DB_fetch_array($KitResult,$db)){
						$NewItem = $KitParts['component'];
						$NewItemQty = $KitParts['quantity'] * $ParentQty;
						$NewPOLine = 0;
						include('includes/SelectOrderItems_IntoCartDCh.inc');
					}

				} elseif ($myrow['mbflag']=='G'){
					prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
				} else { /*Its not a kit set item*/
					include('includes/SelectOrderItems_IntoCartDCh.inc');
				}
			}
		 }
		 unset($NewItem);
	 } /* end of if quick entry */

	if (isset($_POST['AssetDisposalEntered'])){ //its an asset being disposed of
		if ($_POST['AssetToDisposeOf'] == 'NoAssetSelected'){ //don't do anything unless an asset is disposed of
			prnMsg(_('No asset was selected to dispose of. No assets have been added to this customer order'),'warn');
		} else { //need to add the asset to the order
			/*First need to create a stock ID to hold the asset and record the sale - as only stock items can be sold
			 * 		and before that we need to add a disposal stock category - if not already created
			 * 		first off get the details about the asset being disposed of */
			 $AssetDetailsResult = DB_query("SELECT  fixedassets.description,
													fixedassets.longdescription,
													fixedassets.barcode,
													fixedassetcategories.costact,
													fixedassets.cost-fixedassets.accumdepn AS nbv
											FROM fixedassetcategories INNER JOIN fixedassets
											ON fixedassetcategories.categoryid=fixedassets.assetcategoryid
											WHERE fixedassets.assetid='" . $_POST['AssetToDisposeOf'] . "'",$db);
			$AssetRow = DB_fetch_array($AssetDetailsResult);

			/* Check that the stock category for disposal "ASSETS" is defined already */
			$AssetCategoryResult = DB_query("SELECT categoryid FROM stockcategory WHERE categoryid='ASSETS'",$db);
			if (DB_num_rows($AssetCategoryResult)==0){
				/*Although asset GL posting will come from the asset category - we should set the GL codes to something sensible
				 * based on the category of the asset under review at the moment - this may well change for any other assets sold subsequentely */

				/*OK now we can insert the stock category for this asset */
				$InsertAssetStockCatResult = DB_query("INSERT INTO stockcategory ( categoryid,
																				categorydescription,
																				stockact)
														VALUES ('ASSETS',
																'" . _('Asset Disposals') . "',
																'" . $AssetRow['costact'] . "')",$db);
			}

			/*First check to see that it doesn't exist already assets are of the format "ASSET-" . $AssetID
			 */
			 $TestAssetExistsAlreadyResult = DB_query("SELECT stockid
														FROM stockmaster
														WHERE stockid ='ASSET-" . $_POST['AssetToDisposeOf']  . "'",
														$db);
			 $j=0;
			while (DB_num_rows($TestAssetExistsAlreadyResult)==1) { //then it exists already ... bum
				$j++;
				$TestAssetExistsAlreadyResult = DB_query("SELECT stockid
														FROM stockmaster
														WHERE stockid ='ASSET-" . $_POST['AssetToDisposeOf']  . '-' . $j . "'",
														$db);
			}
			if ($j>0){
				$AssetStockID = 'ASSET-' . $_POST['AssetToDisposeOf']  . '-' . $j;
			} else {
				$AssetStockID = 'ASSET-' . $_POST['AssetToDisposeOf'];
			}
			if ($AssetRow['nbv']==0){
				$NBV = 0.001; /* stock must have a cost to be invoiced if the flag is set so set to 0.001 */
			} else {
				$NBV = $AssetRow['nbv'];
			}
			/*OK now we can insert the item for this asset */
			$InsertAssetAsStockItemResult = DB_query("INSERT INTO stockmaster ( stockid,
																				description,
																				longdescription,
																				categoryid,
																				mbflag,
																				controlled,
																				serialised,
																				taxcatid,
																				materialcost)
										VALUES ('" . $AssetStockID . "',
												'" . DB_escape_string($AssetRow['description']) . "',
												'" . DB_escape_string($AssetRow['longdescription']) . "',
												'ASSETS',
												'D',
												'0',
												'0',
												'" . $_SESSION['DefaultTaxCategory'] . "',
												'". $NBV . "')" , $db);
			/*not forgetting the location records too */
			$InsertStkLocRecsResult = DB_query("INSERT INTO locstock (loccode,
																	stockid)
												SELECT loccode, '" . $AssetStockID . "'
												FROM locations",$db);
			/*Now the asset has been added to the stock master we can add it to the sales order */
			$NewItemDue = date($_SESSION['DefaultDateFormat']);
			if (isset($_POST['POLine'])){
				$NewPOLine = $_POST['POLine'];
			} else {
				$NewPOLine = 0;
			}
			$NewItem = $AssetStockID;
			include('includes/SelectOrderItems_IntoCartDCh.inc');
		} //end if adding a fixed asset to the order
	} //end if the fixed asset selection box was set

	 /*Now do non-quick entry delete/edits/adds */

	if ((isset($_SESSION['Items'.$identifier])) OR isset($NewItem)){

		if(isset($_GET['Delete'])){
			//page called attempting to delete a line - GET['Delete'] = the line number to delete
			$QuantityAlreadyDelivered = $_SESSION['Items'.$identifier]->Some_Already_Delivered($_GET['Delete']);
			if($QuantityAlreadyDelivered == 0){
				$_SESSION['Items'.$identifier]->remove_from_cart($_GET['Delete'], 'Yes', $identifier);  /*Do update DB */
			} else {
				$_SESSION['Items'.$identifier]->LineItems[$_GET['Delete']]->Quantity = $QuantityAlreadyDelivered;
			}
		}		if(isset($_GET['DeleteOption'])){
		$SQL='DELETE FROM dcoptions where orderno="'.$_SESSION['ExistingOrder'.$identifier].'"
			AND lineno="'.$_GET['line'].'" AND optionno="'.$_GET['option'].'"';
			DB_query($SQL,$db);
			$SQLD = "

UPDATE dcoptions
SET optionno = (SELECT (@rownum := @rownum + 1) FROM (SELECT @rownum := -1) r) where 
orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'AND lineno='".$_GET['line']."'";
DB_query($SQLD,$db);
			$SQL='DELETE FROM dcdetails where orderno="'.$_SESSION['ExistingOrder'.$identifier].'"
			AND orderlineno="'.$_GET['line'].'" AND lineoptionno="'.$_GET['option'].'"';
DB_query($SQL,$db);
			$SQLD = "

UPDATE dcdetails
SET internalitemno = (SELECT (@rownum := @rownum + 1) FROM (SELECT @rownum := -1) r) where 
orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'";
DB_query($SQLD,$db);
$SQL='SELECT MAX(lineoptionno) as maxoption FROM dcdetails WHERE orderno="'.$_SESSION['ExistingOrder'.$identifier].'"
			AND orderlineno="'.$_GET['line'].'"';
$result=DB_query($SQL,$db);
$row=DB_fetch_array($result);
for($i=$_GET['option'];$i<$row['maxoption'];$i++)
{
$j=$i+1;		
			$SQLD = "

UPDATE dcdetails
SET lineoptionno = '".$i."' where 
orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'AND orderlineno='".$_GET['line']."'
AND lineoptionno='".$j."'
" ;

DB_query($SQLD,$db);
}			
echo'<script>
window.location.assign("'.$RootPath.'/SelectOrderItemsDCh.php?ModifyOrderNumber=' . $_SESSION['ExistingOrder' . $identifier] . '&salescaseref=' . $_SESSION['salescaseref'] . '");
		</script>';

		}
		if(isset($_GET['DeleteLine'])){
				$SQL='DELETE FROM dclines where orderno="'.$_SESSION['ExistingOrder'.$identifier].'"
			AND lineno="'.$_GET['line'].'"';
DB_query($SQL,$db);
			$SQLD = "UPDATE dclines
SET lineno = (SELECT (@rownum := @rownum + 1) FROM (SELECT @rownum := -1) r) where 
orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'";
$SQLline='SELECT MAX(lineno) as maxline FROM dcoptions WHERE orderno="'.$_SESSION['ExistingOrder'.$identifier].'"
		';
$resultline=DB_query($SQLline,$db);
$rowline=DB_fetch_array($resultline);
 $rowline['maxline'];
DB_query($SQLD,$db);
			$SQL='DELETE FROM dcoptions where orderno="'.$_SESSION['ExistingOrder'.$identifier].'"
			AND lineno="'.$_GET['line'].'"';
DB_query($SQL,$db);



for($i=$_GET['line'];$i<$rowline['maxline'];$i++)
{

$j=$i+1;		
			$SQLD = "

UPDATE dcoptions
SET lineno = '".$i."' where 
orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'AND lineno='".$j."'

" ;

DB_query($SQLD,$db);
}	
		
			$SQL='DELETE FROM dcdetails where orderno="'.$_SESSION['ExistingOrder'.$identifier].'"
			AND orderlineno="'.$_GET['line'].'"
			';
			DB_query($SQL,$db);
			$SQLD = "

UPDATE dcdetails
SET internalitemno = (SELECT (@rownum := @rownum + 1) FROM (SELECT @rownum := -1) r) where 
orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'";
DB_query($SQLD,$db);			

 $SQL='SELECT MAX(orderlineno) as maxline FROM dcdetails WHERE orderno="'.$_SESSION['ExistingOrder'.$identifier].'"
			';
$result=DB_query($SQL,$db);
$row=DB_fetch_array($result);
for($i=$_GET['line'];$i<$row['maxline'];$i++)
{

$j=$i+1;		

			$SQLD = "

UPDATE dcdetails
SET orderlineno = '".$i."' where 
orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'AND orderlineno='".$j."'

" ;
DB_query($SQLD,$db);
}		
echo'<script>
window.location.assign("'.$RootPath.'/SelectOrderItemsDCh.php?ModifyOrderNumber=' . $_SESSION['ExistingOrder' . $identifier] . '&salescaseref=' . $_SESSION['salescaseref'] . '");
			</script>';
	
		}

		$AlreadyWarnedAboutCredit = false;
		
		//attempting to update external lines
	//echo "<script> document.getElementById('Recalculate').click();</script>";
		if (isset($_POST['Recalculate']) OR isset($_POST['DeliveryDetails'])){
			
				$linecounter = -1;
		//		print_r($_SESSION['Items'.$identifier]);
		foreach ($_SESSION['Items'.$identifier]->externallines as $externalline) {
		$linecounter++;
		 $clientrequirements = $_POST['clientreq' . $externalline->externallineno];
	//	 $clientrequirements =  htmlspecialchars($clientrequirements);
		//$clientrequirements = str_replace('\r\n','',$clientrequirements);
	//	$clientrequirements = preg_replace("/\\\r\\\n|\\\r|\\\n/",'<br/>',$clientrequirements);
	//			$clientrequirements = stripslashes($clientrequirements);
		$clientrequirements= preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$clientrequirements);
		$clientrequirements= preg_replace('#(/<br/><br/>/)#','<br/>',$clientrequirements);
		$clientrequirements= preg_replace('#(/<p>)#','',$clientrequirements);
		$clientrequirements= preg_replace('#(/</p>)#','',$clientrequirements);
		
		$clientrequirements= html_entity_decode($clientrequirements);
		
		$lineindex = $_POST['lineindex' . $externalline->externallineno];
	$sqlex = "UPDATE dclines SET clientrequirements='" . $clientrequirements . "',
															orderno=" . $_SESSION['ExistingOrder'.$identifier] . "
															
								WHERE lineindex=" . $externalline->externallineindex;
			$result = DB_query($sqlex
													, $db
				, _('The external line number') . ' ' . $lineindex .  ' ' . _('could not be updated'));
			$externalline->clientreq = '';
			$externalline->clientreq = $clientrequirements;
			
			$optioncounter=-1;
			// counter for line options
			foreach ($externalline->lineoptions as $lineoption) {
				echo"<br>";
		//		print_r($lineoption);
		 $optioncounter++;
	//	 $linecounter."_".$optioncounter;
	
		$optiontext = $_POST['SAHdesc' . $linecounter."_".$optioncounter];
		//  $optiontext = htmlspecialchars($optiontext);
		// $optiontext = str_replace('\r\n','',$optiontext);
		$optiontext =  preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$optiontext);
		 $optionindex =$_POST['optionindex' . $linecounter."_".$optioncounter];
		// 	 $optionindex= preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','', $optionindex);

		  $stockstatus =$_POST['stockstatus' . $linecounter."_".$optioncounter];
		   $quantity =$_POST['quantity' . $linecounter."_".$optioncounter];
		     $optiontext= html_entity_decode($optiontext);
	     $optiontext= html_entity_decode($optiontext);
			 $conn=mysqli_connect('localhost','irtiza','netetech321','sahamid');
$SQL = "SELECT salesman from salescase where salescaseref='".$_SESSION['salescaseref']."'";
		$result = DB_query($SQL,$db);
		$rowsp=DB_fetch_array($result);	
	//	echo $rowsp['salesman'];
 $SQL="SELECT stkcode,quantity from dcdetails where orderno=" . $_SESSION['ExistingOrder'.$identifier] . "
AND orderlineno=".$linecounter." AND lineoptionno=".$optioncounter."";

$result=DB_query($SQL,$db);
$count=0;

while($row=DB_fetch_array($result))
{
	$QOHSQL = "SELECT issued AS qoh
							   FROM stockissuance
							   WHERE stockissuance.stockid='" .$row['stkcode'] . "' AND
							   stockissuance.salesperson = '" . $rowsp['salesman'] . "'";
		$QOHResult =  DB_query($QOHSQL,$db);
				$QOHRow = DB_fetch_array($QOHResult);
				$QOH = $QOHRow['qoh'];
	$SQLA='SELECT quantity from dcoptions WHERE orderno="'.$_SESSION['orderno'].'"
	AND lineno="'.$linecounter.'" AND optionno="'.$optioncounter.'"';
	$ResultA=DB_query($SQLA,$db);
	$RowA=DB_fetch_array($ResultA);
	$SUMDC=0;
 $SQLlo="SELECT orderno,quantity,orderlineno,lineoptionno FROM dcdetails 
  WHERE orderno='".$_SESSION['orderno']."'
and stkcode='".$row['stkcode']."' AND orderlineno!=".$linecounter."";
	

$resultlo=mysqli_query($conn,$SQLlo);
WHILE($rowlo=mysqli_fetch_assoc($resultlo))
{ $SQL1A="SELECT quantity as qtyqopt FROM dcoptions WHERE 
dcoptions.orderno=".$rowlo['orderno']."
AND lineno=".$rowlo['orderlineno']."
AND optionno=".$rowlo['lineoptionno']."
";

$result1A=mysqli_query($conn,$SQL1A);
$row1A=mysqli_fetch_assoc($result1A);
//print_r($row1A);
$rowlo['quantity'];
  $row1A['qtyqopt'];
 $SUMDC=$SUMDC+$rowlo['quantity']*$row1A['qtyqopt'];
}

 $OPTQ=$quantity*$row['quantity']+$SUMDC;
//echo $quantity."<br>";
 $SUMDC."<br>";
//echo $row['quantity']."<br>";

//	echo $OPTQ."<br>";

//	echo $row['stkcode']."-".$QOH."-".$OPTQ."<br>";
	if($QOH<$OPTQ){
		
		
		$count++;}
	
}
if($count==0)
{
	if (isset($optionindex))
	{ $sqllo = "UPDATE dcoptions SET optiontext='" . $optiontext. "',
													stockstatus='" . $stockstatus . "',
													quantity='" . $quantity . "',
															orderno=" . $_SESSION['ExistingOrder'.$identifier] . "
															
								WHERE optionindex=" . $optionindex;
								
			$result = DB_query($sqllo, $db
				, _('The option number') . ' ' . $optionindex .  ' ' . _('could not be updated'));
				
				$lineoption->optiontext = $optiontext;
				$lineoption->stockstatus = $stockstatus;
				$lineoption->quantity = $quantity;
				//$lineoption->optiontext = $optiontext;
	}
			}
else {
	$lineoption->quantity = 0;
			prnMsg(_('The item code') . ' ' . $NewItem . ' ' . _('Quantity is more than issued stock'),'warn');
	     }	
		}
		}
		}

		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			//echo $OrderLine->LineNumber;
			if (isset($_POST['Quantity_' . $OrderLine->LineNumber])){

				$Quantity = round(filter_number_format($_POST['Quantity_' . $OrderLine->LineNumber]),$OrderLine->DecimalPlaces);

				if (ABS($OrderLine->Price - filter_number_format($_POST['Price_' . $OrderLine->LineNumber]))>0.01){
					/*There is a new price being input for the line item */

					$Price = filter_number_format($_POST['Price_' . $OrderLine->LineNumber]);
					$_POST['GPPercent_' . $OrderLine->LineNumber] = (($Price*(1-(filter_number_format($_POST['Discount_' . $OrderLine->LineNumber])/100))) - $OrderLine->StandardCost*$ExRate)/($Price *(1-filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]))/100);

				} elseif (ABS($OrderLine->GPPercent - filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]))>=0.01) {
					/* A GP % has been input so need to do a recalculation of the price at this new GP Percentage */


					//prnMsg(_('Recalculated the price from the GP % entered - the GP % was') . ' ' . $OrderLine->GPPercent . '  the new GP % is ' . filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]),'info');


					$Price = ($OrderLine->StandardCost*$ExRate)/(1 -((filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]) + filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]))/100));
				} else {
					$Price = filter_number_format($_POST['Price_' . $OrderLine->LineNumber]);
				}
				$DiscountPercentage = filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]);
				if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
					$Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
				} else {
					$Narrative = '';
				}

				if (!isset($OrderLine->DiscountPercent)) {
					$OrderLine->DiscountPercent = 0;
				}

				if(!Is_Date($_POST['ItemDue_' . $OrderLine->LineNumber])) {
					prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $ItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
					//Attempt to default the due date to something sensible?
					$_POST['ItemDue_' . $OrderLine->LineNumber] = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
				}
				if ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100){
					prnMsg(_('The item could not be updated because you are attempting to set the quantity ordered to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'),'warn');
				} elseif($_SESSION['Items'.$identifier]->Some_Already_Delivered($OrderLine->LineNumber)!=0 AND $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->Price != $Price) {
					prnMsg(_('The item you attempting to modify the price for has already had some quantity invoiced at the old price the items unit price cannot be modified retrospectively'),'warn');
				} elseif($_SESSION['Items'.$identifier]->Some_Already_Delivered($OrderLine->LineNumber)!=0 AND $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->DiscountPercent != ($DiscountPercentage/100)) {

					prnMsg(_('The item you attempting to modify has had some quantity invoiced at the old discount percent the items discount cannot be modified retrospectively'),'warn');

				} elseif ($_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->QtyInv > $Quantity){
					prnMsg( _('You are attempting to make the quantity ordered a quantity less than has already been invoiced') . '. ' . _('The quantity delivered and invoiced cannot be modified retrospectively'),'warn');
				} elseif ($OrderLine->Quantity !=$Quantity
							OR $OrderLine->Price != $Price
							OR ABS($OrderLine->DiscountPercent - $DiscountPercentage/100) >0.001
							OR $OrderLine->Narrative != $Narrative
							OR $OrderLine->ItemDue != $_POST['ItemDue_' . $OrderLine->LineNumber]
							OR $OrderLine->POLine != $_POST['POLine_' . $OrderLine->LineNumber]) {

					$WithinCreditLimit = true;

					if ($_SESSION['CheckCreditLimits'] > 0 AND $AlreadyWarnedAboutCredit==false){
						/*Check credit limits is 1 for warn breach their credit limit and 2 for prohibit sales */
						$DifferenceInOrderValue = ($Quantity*$Price*(1-$DiscountPercentage/100)) - ($OrderLine->Quantity*$OrderLine->Price*(1-$OrderLine->DiscountPercent));

						$_SESSION['Items'.$identifier]->CreditAvailable -= $DifferenceInOrderValue;

						if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
							prnMsg(_('The customer account will breach their credit limit'),'warn');
							$AlreadyWarnedAboutCredit = true;
						} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
							prnMsg(_('This change would put the customer over their credit limit and is prohibited'),'warn');
							$WithinCreditLimit = false;
							$_SESSION['Items'.$identifier]->CreditAvailable += $DifferenceInOrderValue;
							$AlreadyWarnedAboutCredit = true;
						}
					}

					if ($WithinCreditLimit){
										$sqllistprice='select materialcost from stockmaster where stockid = "'.$OrderLine->StockID.'"';
				$sqllistpriceresult=DB_query($sqllistprice,$db);
				$sqllistpriceresultrow = DB_fetch_array($sqllistpriceresult);
			
						$_SESSION['Items'.$identifier]->update_cart_item($OrderLine->LineNumber,
																		$Quantity,
																		$sqllistpriceresultrow['materialcost'],
																		($DiscountPercentage/100),
																		$Narrative,
																		'Yes', /*Update DB */
																		$_POST['ItemDue_' . $OrderLine->LineNumber],
																		$_POST['POLine_' . $OrderLine->LineNumber],
																		filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]),
																		$identifier);
																		
																		
					} //within credit limit so make changes
				} //there are changes to the order line to process
			} //page not called from itself - POST variables not set
		} // Loop around all items on the order


		/* Now Run through each line of the order again to work out the appropriate discount from the discount matrix */
		$DiscCatsDone = array();
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

			if ($OrderLine->DiscCat !='' AND ! in_array($OrderLine->DiscCat,$DiscCatsDone)){
				$DiscCatsDone[]=$OrderLine->DiscCat;
				$QuantityOfDiscCat = 0;

				foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine_2) {
					/* add up total quantity of all lines of this DiscCat */
					if ($OrderLine_2->DiscCat==$OrderLine->DiscCat){
						$QuantityOfDiscCat += $OrderLine_2->Quantity;
					}
				}
				$result = DB_query("SELECT MAX(discountrate) AS discount
									FROM discountmatrix
									WHERE salestype='" .  $_SESSION['Items'.$identifier]->DefaultSalesType . "'
									AND discountcategory ='" . $OrderLine->DiscCat . "'
									AND quantitybreak <= '" . $QuantityOfDiscCat ."'",$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]==NULL){
					$DiscountMatrixRate = 0;
				} else {
					$DiscountMatrixRate = $myrow[0];
				}
				if ($myrow[0]!=0){ /* need to update the lines affected */
					foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine_2) {
						if ($OrderLine_2->DiscCat==$OrderLine->DiscCat){
							$_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->DiscountPercent = $DiscountMatrixRate;
							$_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->GPPercent = (($_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->Price*(1-$DiscountMatrixRate)) - $_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->StandardCost*$ExRate)/($_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->Price *(1-$DiscountMatrixRate)/100);
						}
					}
				}
			}
		} /* end of discount matrix lookup code */
	} // the order session is started or there is a new item being added
	if (isset($_POST['DeliveryDetails'])){
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/DeliveryDetailsDCh.php?identifier='.$identifier . '">';
		prnMsg(_('You should automatically be forwarded to the entry of the delivery details page') . '. ' . _('if this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' .
		   '<a href="' . $RootPath . '/DeliveryDetailsDCh.php?identifier='.$identifier . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
	   	exit;
	}


	if (isset($NewItem)){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		$sql = "SELECT stockmaster.mbflag
		   		FROM stockmaster
				WHERE stockmaster.stockid='". $NewItem ."'";

		$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

		$KitResult = DB_query($sql, $db,$ErrMsg);

		$NewItemQty = 1; /*By Default */
		$Discount = 0; /*By default - can change later or discount category override */

		if ($myrow=DB_fetch_array($KitResult)){
		   	if ($myrow['mbflag']=='K'){	/*It is a kit set item */
				$sql = "SELECT bom.component,
							bom.quantity
						FROM bom
						WHERE bom.parent='" . $NewItem . "'
						AND bom.effectiveto > '" . Date('Y-m-d') . "'
						AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

				$ErrMsg = _('Could not retrieve kitset components from the database because');
				$KitResult = DB_query($sql,$db,$ErrMsg);

				$ParentQty = $NewItemQty;
				while ($KitParts = DB_fetch_array($KitResult,$db)){
					$NewItem = $KitParts['component'];
					$NewItemQty = $KitParts['quantity'] * $ParentQty;
					$NewPOLine = 0;
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					include('includes/SelectOrderItems_IntoCartDCh.inc');
				}

			} else { /*Its not a kit set item*/
				$NewItemDue = date($_SESSION['DefaultDateFormat']);
				$NewPOLine = 0;

				include('includes/SelectOrderItems_IntoCartDCh.inc');
			}

		} /* end of if its a new item */

	} /*end of if its a new item */

	if (isset($NewItemArray) AND isset($_POST['SelectingOrderItems'])){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		$AlreadyWarnedAboutCredit = false;
		foreach($NewItemArray as $NewItem => $NewItemQty) {
			if($NewItemQty > 0)	{
				$sql = "SELECT stockmaster.mbflag
						FROM stockmaster
						WHERE stockmaster.stockid='". $NewItem ."'";

				$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

				$KitResult = DB_query($sql, $db,$ErrMsg);

				//$NewItemQty = 1; /*By Default */
				$Discount = 0; /*By default - can change later or discount category override */

				if ($myrow=DB_fetch_array($KitResult)){
					if ($myrow['mbflag']=='K'){	/*It is a kit set item */
						$sql = "SELECT bom.component,
										bom.quantity
								FROM bom
								WHERE bom.parent='" . $NewItem . "'
								AND bom.effectiveto > '" . Date('Y-m-d') . "'
								AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

						$ErrMsg = _('Could not retrieve kitset components from the database because');
						$KitResult = DB_query($sql,$db,$ErrMsg);

						$ParentQty = $NewItemQty;
						while ($KitParts = DB_fetch_array($KitResult,$db)){
							$NewItem = $KitParts['component'];
							$NewItemQty = $KitParts['quantity'] * $ParentQty;
							$NewItemDue = date($_SESSION['DefaultDateFormat']);
							$NewPOLine = 0;
							include('includes/SelectOrderItems_IntoCartDCh.inc');
						}

					} else { /*Its not a kit set item*/
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = 0;
						include('includes/SelectOrderItems_IntoCartDCh.inc');
					}
				} /* end of if its a new item */
			} /*end of if its a new item */
		}/* loop through NewItem array */
	} /* if the NewItem_array is set */

	/* Run through each line of the order and work out the appropriate discount from the discount matrix */
	$DiscCatsDone = array();
	$counter =0;
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

		if ($OrderLine->DiscCat !="" AND ! in_array($OrderLine->DiscCat,$DiscCatsDone)){
			$DiscCatsDone[$counter]=$OrderLine->DiscCat;
			$QuantityOfDiscCat =0;

			foreach ($_SESSION['Items'.$identifier]->LineItems as $StkItems_2) {
				/* add up total quantity of all lines of this DiscCat */
				if ($StkItems_2->DiscCat==$OrderLine->DiscCat){
					$QuantityOfDiscCat += $StkItems_2->Quantity;
				}
			}
			$result = DB_query("SELECT MAX(discountrate) AS discount
								FROM discountmatrix
								WHERE salestype='" .  $_SESSION['Items'.$identifier]->DefaultSalesType . "'
								AND discountcategory ='" . $OrderLine->DiscCat . "'
								AND quantitybreak <= '" . $QuantityOfDiscCat . "'",$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0] == NULL){
				$DiscountMatrixRate = 0;
			} else {
				$DiscountMatrixRate = $myrow[0];
			}
			foreach ($_SESSION['Items'.$identifier]->LineItems as $StkItems_2) {
				if ($StkItems_2->DiscCat==$OrderLine->DiscCat){
					$_SESSION['Items'.$identifier]->LineItems[$StkItems_2->LineNumber]->DiscountPercent = $DiscountMatrixRate;
				}
			}
		}
	} /* end of discount matrix lookup code */
	
/*	for($i=0;$i<=1;$i++)
	{		
	echo "<h3>Line ".$i."</h3>";
		for($j=0;$i<=1;$i++)
	{	
			echo "<h4>Option ".$j."</h4>";
			{*/
		//	print_r($_SESSION['Items'.$identifier]->LineItems);
$sqlL="select MAX(lineno) as maxlineno from dclines where orderno = ".$_SESSION['ExistingOrder' .$identifier].""; 		
$result = DB_query($sqlL,$db);
$resultrow=DB_fetch_array($result);
//echo '<b>'.$resultrow['maxlineno'].'</b>';
//echo '"Location: '. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier .'"';
if (isset($_POST['newline']) AND $_SESSION['flag']!="up")
{
	$linescount = $resultrow['maxlineno'];
	$linescount++;
	$sql='INSERT into dclines (orderno,lineno) VALUES ("'.$_SESSION['ExistingOrder' .$identifier].'",
	'.$linescount.'
	
	)';
	DB_query($sql,$db);
	
	$newlinesql='SELECT * FROM dclines where orderno = "'.$_SESSION['ExistingOrder' .$identifier].'"
	AND lineno='.$linescount.'
	';
	$newlineresult = DB_query($newlinesql,$db);
	$newlinerow = DB_fetch_array($newlineresult);
		$_SESSION['Items'.$identifier]->externallines[$linescount] = new externalline(
		$newlinerow['lineindex'],
		$newlinerow['orderno'],
		$newlinerow['lineno'],
		$newlinerow['clientrequirements']
		
		
		);
	
	
	$sql='INSERT into dcoptions (orderno,lineno,optionno) VALUES ("'.$_SESSION['ExistingOrder' .$identifier].'",
	"'.$linescount.'",
	0
	
	)';
	DB_query($sql,$db);
	
	$newoptionsql='SELECT * FROM dcoptions where orderno = "'.$_SESSION['ExistingOrder' .$identifier].'"
	AND lineno='.$linescount.' AND optionno = 0
	';
	$newoptionresult = DB_query($newoptionsql,$db);
	$newoptionrow = DB_fetch_array($newoptionresult);
		$_SESSION['Items'.$identifier]->externallines[$linescount]->lineoptions[0] = new lineoption(
		$newoptionrow['optionindex'],
		$newoptionrow['orderno'],
		$newoptionrow['lineno'],
		$newoptionrow['optionno'],
		$newoptionrow['optiontext'],
		$newoptionrow['quantity'],
		$newoptionrow['stockstatus']
		
		
		);
//echo"abc";	
//$_POST=array();

//print_r($_POST);
//unset($_POST['newline']);	
//echo "xyz";

//header('"Location: sahamIdtest/SelectOrderItemsDCh.php?identifier=?identifier='.$identifier .'"' );
}

else {
	
	
	$linescount = $resultrow['maxlineno'];
	
}
for ($line=0; $line<= $linescount; $line++ )
{
	
	$sqlO="select MAX(optionno) as maxlineoptionno from dcoptions where orderno = ".$_SESSION['ExistingOrder' .$identifier]."
AND lineno = ".$line.""; 		
$resultO = DB_query($sqlO,$db);
$resultrowO=DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";
echo'<input type="hidden" name = "lineindex'.$line.'"
	value="'.$_SESSION['Items'.$identifier]->externallines[$line]->externallineno.'" >';
	//$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowC['optiontext']);
//$_SESSION['Items'.$identifier]->externallines[$line]->clientreq = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n|\r|\r\n\n)#',
//	'',$_SESSION['Items'.$identifier]->externallines[$line]->clientreq);

echo '<table width="100%" cellpadding="2" "><tr><td colspan="8">
<h3 style = "visibilty:hidden>Line No. '.($line+1).' Client Requirements </h3><textarea  style=" visibilty:hidden; width: 700px; height: 100%;" id = "clientreq'.$line.'"
 name = "clientreq'.$line.'" cols="90" rows="10">

'.html_entity_decode($_SESSION['Items'.$identifier]->externallines[$line]->clientreq).'
</textarea>

</tr></td></table>
';
//echo $_SESSION['Items'.$identifier]->externallines[$line]->clientreq;
	$str =	preg_replace("/(<br\s*\/?>\s*)+/", "<br/>", html_entity_decode($_SESSION['Items'.$identifier]->externallines[$line]->clientreq));
	
	
	echo'
		<script type="text/javascript">
    var editor =  textboxio.replace("#clientreq'.$line.'", {
          
		  
		   codeview : {
        enabled: false,
        showButton: false
    },
	  ui: {
        toolbar: {

            items: ["undo"]
        }
    },

		  paste: {
            style: "plain" },
          css: {
            stylesheets: ["example.css"]
          }
        });
		
	 editor.content.set("'.$str.'");
  </script>';	

	//<td><b>Client Requirements and S A H Description</b><textarea cols="90" rows="10"></textarea></td>
	$varopt='newoption'.$line;
/*	if (isset($_POST[$varopt]) AND $_SESSION['flag']!="up")
{
	$optionscount = $resultrowO['maxlineoptionno'] + 1;
$sql='INSERT into dcoptions (orderno,lineno,optionno) VALUES ("'.$_SESSION['ExistingOrder' .$identifier].'",
	"'.$line.'",
	"'.$optionscount.'"
	
	)';
	DB_query($sql,$db);
	
		$newoptionsql='SELECT * FROM dcoptions where orderno = "'.$_SESSION['ExistingOrder' .$identifier].'"
	AND lineno='.$line.' AND optionno = '.$optionscount.'
	';
	$newoptionresult = DB_query($newoptionsql,$db);
	$newoptionrow = DB_fetch_array($newoptionresult);
		$_SESSION['Items'.$identifier]->externallines[$line]->lineoptions[$optionscount] = new lineoption(
		$newoptionrow['optionindex'],
		$newoptionrow['orderno'],
		$line,
		$newoptionrow['optionno'],
		$newoptionrow['optiontext'],
		$newoptionrow['quantity'],
		$newoptionrow['stockstatus']
		
		);
	
	
	
	unset($_POST[$varopt]);
}
else
{
	$optionscount = $resultrowO['maxlineoptionno'];
	
	
}
*/
for ($option=0; $option<= $optionscount; $option++ )
	{
		$count++;
		//echo $count;
		//echo "<h3>Line No. ".($i+1)."</h3><br>";
		echo '<table width="90%" cellpadding="2">
		<tr><td>
		<h2>Line No. '.($line+1).'</h2>
		</td>';
		if($_SESSION['ExistingOrder' . $identifier]!=$_GET['identifier'])
	//	echo'<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier=' . $identifier . '&amp;DeleteOption=1&amp;line=' . $line . '&amp;option=' . $option . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">Delete Option</a></td></tr>';

		echo'<tr></table>';
		echo '<br>';
		echo'	<table width="90%" cellpadding="2">';
//print_r($_SESSION['Items'.$identifier]);
	
/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

     /*   echo '<div class="page_help_text">' . _('Quantity (required) - Enter the number of units ordered.  Price (required) - Enter the unit price.  Discount (optional) - Enter a percentage discount.  GP% (optional) - Enter a percentage Gross Profit (GP) to add to the unit cost.  Due Date (optional) - Enter a date for delivery.') . '</div><br />';
		echo '<br />*/
	
			
				echo '<tr>';
		
	//		echo '<th>' . _('Order Line No') . '</th>';
	//		echo '<th>' . _('Line Option No') . '</th>';
	//	echo '<th>' . _('Option Item No') . '</th>';
		echo '<th>' . _('Item Code') . '</th>
				
				<th>' . _('Item Description') . '</th>
				<th>' . _('Quantity') . '</th>
				
				
				<th>' . _('Last Cost') . '</th>
				<th>' . _('Last Update') . '</th>
				<th>' . _('Updated By') . '</th>
				<th>' . _('QOH') . '</th>
				<th>' . _('Price') . '</th>';

		
			echo '<th>' . _('Discount') . '</th>
		';
		echo '<th>' . _('Unit Rate') . '</th>
		';
		echo '<th>' . _('Total') . '</th>
				<th>' . _('Due Date') . '</th></tr>';

		$_SESSION['Items'.$identifier]->total = 0;
		$_SESSION['Items'.$identifier]->totalVolume = 0;
		$_SESSION['Items'.$identifier]->totalWeight = 0;
		$k =0;  //row colour counter
		
	
		
		
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			
		//	$UnitRate = $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
		if( $_POST['Discount_' . $OrderLine->LineNumber ] != 0)
		{
			
			$LineDiscount = $_POST['Discount_' . $OrderLine->LineNumber ]/100;
			
			$UnitRate = $OrderLine->Price * (1 - $_POST['Discount_' . $OrderLine->LineNumber ]/100);
			
		}
			else
			{
				$UnitRate = $_POST['UnitRate_' . $OrderLine->LineNumber ];
		$LineDiscount = 1 - $UnitRate/$OrderLine->Price;
				
			}
				
						$LineTotal = $OrderLine->Quantity * $UnitRate;

			$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
			$DisplayDiscount = ($OrderLine->DiscountPercent * 100);
			$QtyOrdered = $OrderLine->Quantity;
			$QtyRemain = $QtyOrdered - $OrderLine->QtyInv;

			if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag=='B' OR $OrderLine->MBflag=='M')) {
				/*There is a stock deficiency in the stock location selected */
				$RowStarter = '<tr style="background-color:#EEAABB">'; //rows show red where stock deficiency
			} elseif ($k==1){
				$RowStarter = '<tr class="OddTableRows">';
				$k=0;
			} else {
				$RowStarter = '<tr class="EvenTableRows">';
				$k=1;
			}

			
         // echo "--".$j."--".$OrderLine->lineoptionno."<br>";
			if(($line== $OrderLine->orderlineno) AND ($option== $OrderLine->lineoptionno) ) {
				echo $RowStarter;
		//		echo '<td><input type="text" name="orderlineno_' .	 $OrderLine->LineNumber . '" value="'.$OrderLine->orderlineno.'" /></td>';
		//		echo '<td><input type="text" name="lineoptionno_' .	 $OrderLine->LineNumber . '" value="'.$OrderLine->lineoptionno.'" /></td>';
		//		echo '<td><input type="text" name="optionitemno_' .	 $OrderLine->LineNumber . '" value="'.$OrderLine->optionitemno.'" /></td>';
			 
			 $sql = 'SELECT lastcost,lastcostupdate, lastupdatedby from stockmaster where stockid = "'.$OrderLine->StockID.'"';
			 $result = mysqli_query($db,$sql);
			 $resultrow = mysqli_fetch_assoc($result);
			 echo '<td>';
			echo '<a href="javascript:void(0);"
 NAME="My Window Name"  title=" My title here "
 onClick=window.open("quotationhistory.php/?stockid='.$OrderLine->StockID.'","Ratting","width=550,height=170,0,status=0,scrollbars=1");>' . $OrderLine->StockID . '</a></td>
					
				<td title="' . $OrderLine->LongDescription . '">' . $OrderLine->ItemDescription . '</td>';
				

			echo '<td><input class="number" tabindex="2" type="text" required="required" 
			id="Quantity_' . $OrderLine->LineNumber . '"
			name="Quantity_' . $OrderLine->LineNumber . '" size="6"
			maxlength="8" 
			value="' . locale_number_format($OrderLine->Quantity,$OrderLine->DecimalPlaces) . '" 
			title="' . _('Enter the quantity of this item ordered by the customer') . '" onkeyup="myFunctionQty()" />';
			if ($QtyRemain != $QtyOrdered){
				echo '<br />' . locale_number_format($OrderLine->QtyInv,$OrderLine->DecimalPlaces) .' ' . _('of') . ' ' . locale_number_format($OrderLine->Quantity,$OrderLine->DecimalPlaces).' ' . _('invoiced');
			}
			
					echo '<td>' . $resultrow['lastcost'] . '</td>';
					echo '<td>' . $resultrow['lastcostupdate'] . '</td>';
					echo '<td>' . $resultrow['lastupdatedby'] . '</td>';
echo '</td>
					<td class="number">' . locale_number_format($OrderLine->QOHatLoc,$OrderLine->DecimalPlaces) . '</td>
					';
				/*OK to display with discount if it is an internal user with appropriate permissions */
				echo '<td><input class="number" type="text" readonly="readonly" required="required" name="Price_' . $OrderLine->LineNumber . '"
				id="Price_' . $OrderLine->LineNumber . '"
				size="16" maxlength="16" 
				value="' . locale_number_format($OrderLine->Price,2) . '" title="' . _('Enter the price to charge the customer for this item') . '" />
				
				<input class="number" type="hidden" readonly="readonly" required="required" name="PriceJS_' . $OrderLine->LineNumber . '"
				id="PriceJS_' . $OrderLine->LineNumber . '"
				size="16" maxlength="16" 
				value="' . $OrderLine->Price . '" title="' . _('Enter the price to charge the customer for this item') . '" />
				
				</td>
					<td><input class="number" type="text" required="required" name="Discount_' . $OrderLine->LineNumber . '"
					id="Discount_' . $OrderLine->LineNumber . '"
					size="5" maxlength="4"
					value="' . ($OrderLine->DiscountPercent * 100) . '" title="' . _('Enter the discount percentage to apply to the price for this item') . '" 
					onkeyup="myFunctionunitrate()"
					/></td>
					';
			
			if ($_SESSION['Items'.$identifier]->Some_Already_Delivered($OrderLine->LineNumber)){
				$RemTxt = _('Clear Remaining');
			} else {
				$RemTxt = _('Delete');
			}
				echo'<td><input class="number" type="text"  name="UnitRate_' . $OrderLine->LineNumber . '" 
				id="UnitRate_' . $OrderLine->LineNumber . '"
				size="16"
					value="' . (1 - $OrderLine->DiscountPercent)*$OrderLine->Price . '" title="' . _('Enter the discount percentage to apply to the price for this item') . '"
							onkeyup="myFunctiondiscount()"/>
						
							
							</td>
					';
			//echo "LineTotal_" . $OrderLine->LineNumber . "";
			echo'<td><input class="number" type="text"  name="LineTotal_' . $OrderLine->LineNumber . '" 
			id="LineTotal_' . $OrderLine->LineNumber . '" 
					value="' . ((1 - $OrderLine->DiscountPercent)*$OrderLine->Price*$OrderLine->Quantity) .
					'" title="' . _('Enter the discount percentage to apply to the price for this item') . '" size="16"
					readonly="readonly" /></td>
					';
			
			
			echo'			<script>
			
			function myFunctionQty() {
				
				var z = 0;
				while (z<=' . $OrderLine->LineNumber . ')
				{
 document.getElementById("LineTotal_"+z).value=  Math.round(document.getElementById("UnitRate_"+z).value * document.getElementById("Quantity_"+z).value*100)/100;
 

			z++;
				}
}
			function myFunctiondiscount() {
				
				var y = 0;
				while (y<=' . $OrderLine->LineNumber . ')
				{
					
					
 document.getElementById("Discount_"+y).value= ( (1 - document.getElementById("UnitRate_"+y).value / document.getElementById("PriceJS_"+y).value)*10000000000/10000000000)*100;
 document.getElementById("LineTotal_"+y).value=  Math.round(document.getElementById("UnitRate_"+y).value * document.getElementById("Quantity_"+y).value*100)/100;
 

			y++;
				}
}
function myFunctionunitrate() {
				var x = 0;
				while (x<=' . $OrderLine->LineNumber . ')
				{
  document.getElementById("UnitRate_"+x).value=Math.round( document.getElementById("PriceJS_"+x).value *(1 - document.getElementById("Discount_"+x).value/100)*100)/100;

 document.getElementById("LineTotal_"+x).value= Math.round(document.getElementById("UnitRate_"+x).value * document.getElementById("Quantity_"+x).value*100)/100;

				x++;
				}
}
			
			
			</script>';
			
		//	echo '<td class="number">' . $DisplayLineTotal . '</td>';
			$LineDueDate = $OrderLine->ItemDue;
			if (!Is_Date($OrderLine->ItemDue)){
				$LineDueDate = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
				$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemDue= $LineDueDate;
			}

			echo '<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ItemDue_' . $OrderLine->LineNumber . '"
			size="10" maxlength="10" value="' . $LineDueDate . '" /></td>';
if($_SESSION['ExistingOrder' . $identifier]!=$_GET['identifier'])
			echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier=' . $identifier . '&amp;Delete=' . $OrderLine->LineNumber . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">' . $RemTxt . '</a></td></tr>';

			
					?>

					<?php
			

			$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $LineTotal;
		//	$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
		//	$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;
			}
		} /* end of loop around items */
		
		$DisplayTotal = locale_number_format($_SESSION['Items'.$identifier]->total,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		
		
			echo'</table>';
			echo'<a   href = "'.$RootPath.'/additemsDCh.php?line='.$line.'
			&option='.$option.'" rel="#overlay" onclick="loadScroll()">
			<h3>Add New Item</h3></a>';
	//		<input type="submit" value="Add New Item" name="addnewitem'.$count.'">';
			
			echo'<input type="hidden" name="count" value="'.$count.'" />';
	//	$DisplayTotal = locale_number_format($_SESSION['Items'.$identifier]->total,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		
		
/*		echo'<table width="90%" cellpadding="2">
		
		<tr>
		<td><b>S A H Description</b><textarea cols="90" rows="10"></textarea></td><td><b>Quantity: </b><input/></td><td><b>Price:</b>'.$DisplayTotal.'</td><td><b>Sub Total:</b>'.$DisplayTotal.'</td>
		</table>
		';	
	
	}
		echo'		<br />
				<div class="centre" >
				
				
					<input type="submit" name="Recalculate" value="' . _('Re-Calculate') . '" />';
	
	
			echo'	
					<input type="submit" name="newoption" value="' . _('Add New Option') . '" />';
	echo "<hr>";
			
	 # end of if lines
}
echo'	<br/>
					<input type="submit" name="newline" value="' . _('Add New Line') . '" />
			


					<input type="submit" name="DeliveryDetails" value="' . _('Enter Delivery Details and Confirm Quotation') . '" />
				</div>
				<br />';
/* Now show the stock item selection search stuff below */
$nm='addnewitem'.$count;

if (isset($_POST[$nm]))
{
	$_SESSION['addnew']=$_POST[$nm];
}
	$_SESSION['orderno']=$_SESSION['ExistingOrder' .$identifier];
	$_SESSION['line']=$line;
	$_SESSION['option']=$option;
	$_SESSION['loccode']=$_SESSION['Items'.$identifier]->Location;

//print_r ($_SESSION[$nm]);
	#end of else not selecting a customer
//print_r ($_SESSION['Items'.$identifier]->externallines[$line]);

//$_SESSION['Items'.$identifier]->externallines[$line]->lineoptions[$option]->optiontext;
$qty = locale_number_format($_SESSION['Items'.$identifier]->externallines[$line]->lineoptions[$option]->quantity,0);
//	$_SESSION['Items'.$identifier]->externallines[$line]->lineoptions[$option]->optiontext = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n|\r|\r\n\n)#',
//	'',$_SESSION['Items'.$identifier]->externallines[$line]->lineoptions[$option]->optiontext);
	
	echo'<table width="100%" cellpadding="2">
		
		<tr>
		<td colspan="8"><h3 style="color:blue;">S A H Description</h3><textarea style="width: 700px; height: 100%;" cols="90" rows="10" id = "SAHdesc'.$line.'_'.$option.'" 
		name = "SAHdesc'.$line.'_'.$option.'">'.html_entity_decode($_SESSION['Items'.$identifier]->externallines[$line]->lineoptions[$option]->optiontext).'
		</textarea></td>
		</tr><tr><td></td><td><input type="hidden" name = "stockstatus'.$line.'_'.$option.'" 
		
		value="'.$_SESSION['Items'.$identifier]->externallines[$line]->lineoptions[$option]->stockstatus.'"/></td><td>
		<b>Quantity: </b><input name = "quantity'.$line.'_'.$option.'"
		value="'.$_SESSION['Items'.$identifier]->externallines[$line]->lineoptions[$option]->quantity.'"/></td><td>
		</td>
		<td><b>Price:</b>'.$DisplayTotal.'</td><td><b>Sub Total:</b>'.
		locale_number_format(
		($qty*
		$_SESSION['Items'.$identifier]->total),2).'</td>
		</table>
		';	
	$str =	preg_replace("/(<br\s*\/?>\s*)+/", "<br/>", html_entity_decode($_SESSION['Items'.$identifier]->externallines[$line]->lineoptions[$option]->optiontext));
		echo'
		<script type="text/javascript">
    var editordesc =  textboxio.replace("#SAHdesc'.$line.'_'.$option.'", {
          paste: {
            style: "plain" },
          css: {
            stylesheets: ["example.css"]
          },
		  
		   codeview : {
        enabled: false,
        showButton: false
    },
	  ui : {
        toolbar :  {
            items : [ "undo","tools"]
        }
    }
        });
		
	 editordesc.content.set("'.$str.'");
  </script>';	

	echo'<input type="hidden" name = "optionindex'.$line.'_'.$option.'"
	value="'.$_SESSION['Items'.$identifier]->externallines[$line]->lineoptions[$option]->lineoptionindex.'" >';
	}
		echo'		<br />
				<div class="centre" >
						<input type="submit" id="Recalculate" name="Recalculate" value="' . _('Save') . '"  onclick="saveScroll()" />

				
				';
	$varoption= "newoption".$line;
	$varoptionindex= "optionindex".$line;
	
		//	echo'	
		//			<input type="submit" name="'.$varoption.'" value="' . _('Add New Option') . '" onclick="saveScroll()" />';
			if($_SESSION['ExistingOrder' . $identifier]!=$_GET['identifier'])
			echo'		<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier=' . $identifier . '&amp;DeleteLine=1&amp;lineno=' .$line . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">Delete Line</a>';

			echo'		<input type="hidden" name="'.$varoptionindex.'" value="' . $line. '" />';
	echo "<hr>";
			
	 # end of if lines
}
echo'	<br/>
						<br/>
					<input type="submit" name="newline" value="' . _('Add New Line') . '" onclick="saveScroll()"/>
			


					<input type="submit" name="DeliveryDetails" value="' . _('Enter Delivery Details and Confirm DC') . '" />
				</div>
				<br />
				
				
				';
				
if(isset($_GET['NewOrder']))
{	
/*echo"
	<script>
document.getElementById('Recalculate').click();  
</script>
	
	";*/
}
	}
?>

<?php

$scrollx = 0;

$scrolly = 0;

if(!empty($_POST['scrollx'])) {

$scrollx = $_POST['scrollx'];

}

if(!empty($_POST['scrolly'])) {

$scrolly = $_POST['scrolly'];

}
$_SESSION['flag']="down";
?>
<?php
//echo "$scrolly";
?>

<!-- overlayed element -->
<script type="text/javascript">

window.scrollTo(<?php echo "$scrollx" ?>, <?php echo "$scrolly" ?>);
//window.scrollBy(0,window.height-getcookie("windowheight");


$(document).ready(function() {

 var tempScrollTop = $(window).scrollTop();
 // Setup a basic iframe for use inside overlays.
 var theframe = $('<iframe name="iframe_style" id="iframe_style" style="position: fixed;	width: 98%;height: 100%;top: 0;left: 0;" frameBorder="0" scrolling="yes"></iframe>');
      // if the function argument is given to overlay,
    // it is assumed to be the onBeforeLoad event listener
	 
 $("a[rel]").overlay({

 
        mask:  {color: '#000',loadSpeed: 100, opacity: '.60'},
    oneInstance: false, 
    closeOnClick: false,
	onClose: function(){
            window.location.reload(true);
        },
        onBeforeLoad: function() {
 
            // grab wrapper element inside content
            var wrap = this.getOverlay().find(".contentWrap");
 
             var link = this.getTrigger().attr("href");
            // load the page specified in the trigger
            $(theframe).attr({ src: link });
            //Write the iframe into the wrap
            wrap.html(theframe);   
        }
				
						
	    
   });
$(window).scrollTop(tempScrollTop);
   });


</script>

<div class="overlay" id="overlay"><!-- the external content is loaded inside this tag --> 
  <div class="contentWrap"></div>
</div>
<?php
include('includes/footer.inc');
?>
