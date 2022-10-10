<?php

/* $Id: InternalStockRequest.php 4576 2011-05-27 10:59:20Z daintree $*/

include('includes/DefineDCClass.php');

include('includes/session.inc');
$Title = _('Create a Delivery Challan');
$ViewTopic = 'Inventory';
$BookMark = 'CreateRequest';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
error_reporting(0);
$salescaseref = $_GET['salescaseref'];

 $sql = "SELECT * from salescase inner join debtorsmaster on salescase.debtorno = debtorsmaster.debtorno
						where salescaseref = '".$salescaseref."'";
						$result=DB_query($sql,$db);
						$rowresult = DB_fetch_array($result);
//var_dump($_POST['Customer'],$_SESSION['Customer']);
//if (isset($_POST['Customer']))
$_SESSION['Customer'] = $rowresult['debtorno'];
//$_POST['Customer'] = $_SESSION['Customer'];

//if (isset($_POST['dba']))
 $_SESSION['dba'] = $rowresult['dba'];
//$_POST['dba'] = $_SESSION['dba'];

if (isset($_POST['CustBranch']))
$_SESSION['CustBranch'] = $_POST['CustBranch'];
if (isset($_POST['CustContact']))
$_SESSION['CustContact'] = $_POST['CustContact'];
//$_POST['CustBranch'] = $_SESSION['CustBranch'];

if (isset($_POST['deliverto']))
$_SESSION['deliverto'] = $_POST['deliverto'];


$DefaultDisplayRecordsMax = 250;
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Inventory Items') . '" alt="" />' . ' ' . _('Inventory Items') . '</p>';
if (isset($_POST['NewUpdate']) or isset($_POST['Next']) or isset($_POST['Previous']) or isset($_POST['Go'])) {
	unset($StockID);
	unset($_SESSION['SelectedStockItem']);
	unset($_POST['Select']);
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
// Always show the Update facilities

if (isset($_GET['New'])) {
	unset($_SESSION['Transfer']);
	$_SESSION['Request'] = new StockRequest();
}

if (isset($_POST['Update'])) {
	$InputError=0;
	
	
	if ($InputError==0) {
		$_SESSION['Request']->Location=$_SESSION['UserStockLocation'];
		
		
		$_SESSION['Request']->DispatchDate=$_POST['DispatchDate'];
		$_SESSION['Request']->deliverto=$_SESSION['destination'];
		$_SESSION['Request']->substore=$_POST['substore'];
		
		$_SESSION['Request']->salesperson=$_SESSION['UsersRealName']; 
		$_SESSION['Request']->po=$_POST['po']; 
		$_SESSION['Request']->ref=$_POST['ref']; 
		
		$_SESSION['Request']->dba=$_SESSION['dba']; 
		$_SESSION['Request']->Customer=$_SESSION['Customer']; 
		$_SESSION['Request']->CustBranch=$_SESSION['CustBranch']; 
		$_SESSION['Request']->CustContact=$_SESSION['CustContact']; 
		
		$_SESSION['Request']->storemanager=$_SESSION['UsersRealName']; 
		
	}
}

if (isset($_POST['Edit'])) {
	$_SESSION['Request']->LineItems[$_POST['LineNumber']]->ItemDescription=$_POST['ItemDescription'];
	
	$_SESSION['Request']->LineItems[$_POST['LineNumber']]->Quantity=$_POST['Quantity'];
	$_SESSION['Request']->LineItems[$_POST['LineNumber']]->Comments=$_POST['Comments'];
	
}

if (isset($_GET['Delete'])) {
	unset($_SESSION['Request']->LineItems[$_GET['Delete']]);
	echo '<br />';
	prnMsg( _('The line was successfully deleted'), 'success');
	echo '<br />';
}

foreach ($_POST as $key => $value) {
	if (mb_strstr($key,'StockID')) {
		$Index=mb_substr($key, 7);
		
		$count = 0;
		if (filter_number_format($_POST['Quantity'.$Index])>0) {
			$StockID=$value;
			$ItemDescription=$_POST['ItemDescription'.$Index];
			$DecimalPlaces=$_POST['DecimalPlaces'.$Index];
			$NewItem_array[$StockID] = filter_number_format($_POST['Quantity'.$Index]);
			
			$NewComment_array[$StockID] =$_POST['Comments'.$Index];
			
			$_POST['Units'.$StockID]=$_POST['Units'.$Index];
			$StockIDarray[$StockID] = $StockID;
			for ($foo = 0; $foo <= count($_SESSION['Request']->LineItems); $foo++) {
			
			//echo $_SESSION['Request']->LineItems[$Index]->StockID;
			if ($_SESSION['Request']->LineItems[$foo]->ItemDescription == $ItemDescription)
			{
				$count++;
			}
			
		}
		
		
		if ($count<1)
		{
			$_SESSION['Request']->AddLine($StockID, $ItemDescription, $NewItem_array[$StockID],$NewComment_array[$StockID], $_POST['Units'.$StockID], $DecimalPlaces);
		
		}
		}
		
	}
}
$count = 0;
foreach ($_SESSION['Request']->LineItems as $LineItems) {
			
			$count++;
		}





if (isset($_POST['Submit']) and $count > 0) {
	
	
	DB_Txn_Begin($db);
	$InputError=0;
	
	if ($_SESSION['Request']->Location=='') {
		prnMsg( _('You must select a Location to request the items from'), 'error');
		$InputError=1;
	}
	if ($InputError==0) {
		$RequestNo = GetNextTransNo(38, $db);
		$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
		
		$_SESSION['tranno'] = $RequestNo;
		 $HeaderSQL="INSERT INTO dc (dispatchid,
										salescaseref,
										contid,
											loccode,
											po,
											ref,
											dba,
											debtorno,
											branchcode,
											despatchdate,
											salesperson,
											
											deliverto
											)
										VALUES(
											'" . $RequestNo . "',
												'" . $salescaseref . "',
												" . $_SESSION['Request']->CustContact . ",
											'" . $_SESSION['Request']->Location . "',
											'" . $_SESSION['Request']->po . "',
											'" . $_SESSION['Request']->ref . "',
											'" . $_SESSION['Request']->dba . "',
											'" . $_SESSION['Request']->Customer . "',
											'" . $_SESSION['Request']->CustBranch . "',
											
											'" . FormatDateForSQL($_SESSION['Request']->DispatchDate) . "',
											
											'" . $_SESSION['Request']->salesperson . "',
											
											'" . $_SESSION['Request']->deliverto . "')";
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);
		
		
		$HeaderSQL="INSERT INTO dcclient (dispatchid,
											loccode,
											po,
											ref,
											dba,
											despatchdate,
											salesperson,
											
											deliverto
									)
										VALUES(
													'" . $RequestNo . "',
											
											'" . $_SESSION['Request']->Location . "',
											'" . $_SESSION['Request']->po . "',
											'" . $_SESSION['Request']->ref . "',
											'" . $_SESSION['Request']->dba . "',
											
											'" . FormatDateForSQL($_SESSION['Request']->DispatchDate) . "',
											
											'" . $_SESSION['Request']->salesperson . "',
											
											'" . $_SESSION['Request']->deliverto . "')";
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);

		
		foreach ($_SESSION['Request']->LineItems as $LineItems) {
		 $LineSQL="INSERT INTO dcitems (dispatchitemsid,
													dispatchid,
													stockid,
													quantity,
													

													decimalplaces,
													uom)
												VALUES(
													'".$LineItems->LineNumber."',
													'".$RequestNo."',
													'".$LineItems->StockID."',
													'".$LineItems->Quantity."',
													
													'".$LineItems->DecimalPlaces."',
													'".$LineItems->UOM."')";
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request line record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($LineSQL,$db);
		 $LineSQL="INSERT INTO dcclientitems (dispatchitemsid,
													dispatchid,
													stockid,
													quantity,
													description,
													decimalplaces,
													uom)
												VALUES(
													'".$LineItems->LineNumber."',
													'".$RequestNo."',
													'".$LineItems->StockID."',
													'".$LineItems->Quantity."',
													'".$LineItems->ItemDescription."',
													'".$LineItems->DecimalPlaces."',
													'".$LineItems->UOM."')";
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request line record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($LineSQL,$db);
						$SQL="SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $LineItems->StockID . "'
						AND loccode= '" . $_SESSION['UserStockLocation'] . "'";

			$ResultQ = DB_query($SQL, $db);
			if (DB_num_rows($ResultQ)==1){
					$LocQtyRow = DB_fetch_row($ResultQ);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}
		
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												reference,
												qty,
												prd,
												newqoh
												)
					
					VALUES (
						'" . $LineItems->StockID . "',
						512,
						'" . $RequestNo . "',
						'" . $_SESSION['Request']->Location . "',
							'" . FormatDateForSQL($_SESSION['Request']->DispatchDate) . "',
						'" . _('Delivered To') . ' ' . DB_escape_string($_SESSION['Request']->deliverto) ."'
						,'" . round($LineItems->Quantity,0). "'
						,'" . $PeriodNo . "'
										
						,'" .round($QtyOnHandPrior,0). "'
						)";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the incoming stock cannot be added because');
				$DbgMsg =  _('The following SQL to insert the stock movement record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


/*
		$EmailSQL="SELECT email
					FROM www_users, departments
					WHERE departments.authoriser = www_users.userid
						AND departments.departmentid = '" . $_SESSION['Request']->Department ."'";
		$EmailResult = DB_query($EmailSQL,$db);
		if ($myEmail=DB_fetch_array($EmailResult)){
			$ConfirmationText = _('An internal stock request has been created and is waiting for your authorization');
			$EmailSubject = _('Internal Stock Request needs your authorization');
			 if($_SESSION['SmtpSetting']==0){
			       mail($myEmail['email'],$EmailSubject,$ConfirmationText);
			}else{
				include('includes/htmlMimeMail.php');
				$mail = new htmlMimeMail();
				$mail->setSubject($EmailSubject);
				$mail->setText($ConfirmationText);
				$result = SendmailBySmtp($mail,array($myEmail['email']));
			}
*/
		
		
	$SQL = "select stockid, salesperson from stockissuance where stockid = '" . $LineItems->StockID . "'
	
	and salesperson = '" . $_SESSION['Request']->salesperson . "'
	
	";
	
				$Result = DB_query($SQL, $db);
				
			if (mysqli_num_rows($Result)>0)
				{
					$SQL = "UPDATE stockissuance
					SET issued = issued - '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND salesperson='" . $_SESSION['Request']->salesperson . "'";
	$Result = DB_query($SQL, $db);
			$SQL = "UPDATE stockissuance
					SET dc = dc + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND salesperson='" . $_SESSION['Request']->salesperson . "'";
	$Result = DB_query($SQL, $db);
			
					
				}
				else
				{
					
					$SQL = "INSERT INTO stockissuance(salesperson,stockid,issued) values
					('" . $_SESSION['Request']->salesperson . "','" . $LineItems->StockID . "'
					,'" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "')";
	$Result = DB_query($SQL, $db);
					
					
					
				}
				
			//$myrow=DB_fetch_array($result);
		}
	}
	DB_Txn_Commit($db);

		echo '<p><a href="'.$RootPath.'/PDFDC.php?RequestNo=' . $RequestNo . '&salescaseref=' . $salescaseref . '">' .  _('Print the DC'). '</a></p>';
	echo '<p><a href="dcclient.php?tranno='.$_SESSION['tranno'].'"> Create DC for client</a></p>';
	
	include('includes/footer.inc');
	unset($_SESSION['Request']);
	exit;
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Dispatch') .
		'" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['Edit'])) {
		echo '<form name="form" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?salescaseref=' . $salescaseref.'" method="post">';
 echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="2"><h4>' . _('Edit the Request Line') . '</h4></th>
		</tr>';
	echo '<tr>
			<td>' . _('Line number') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->LineNumber . '</td>
		</tr>
		<tr>
			<td>' . _('Stock Code') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->StockID . '</td>
		</tr>
		<tr>
			<td>' . _('Item Description') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->ItemDescription . '</td>
		</tr>
		<tr>
			<td>' . _('Unit of Measure') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->UOM . '</td>
		</tr>
		<tr>
			<td>' . _('Quantity Requested') . '</td>
			<td><input type="text" class="number" name="Quantity" value="' . locale_number_format($_SESSION['Request']->LineItems[$_GET['Edit']]->Quantity, $_SESSION['Request']->LineItems[$_GET['Edit']]->DecimalPlaces) . '" /></td>
			<td>' . _('Comments') . '</td>
			<td><input type="text" name="Comments" value="' . $_SESSION['Request']->LineItems[$_GET['Edit']]->Comments. '" /></td>
		
		</tr>';
	echo '<input type="hidden" name="LineNumber" value="' . $_SESSION['Request']->LineItems[$_GET['Edit']]->LineNumber . '" />';
	echo  $_SESSION['Request']->LineItems[$_GET['Edit']]->LineNumber;
	echo '</table>
		<br />';
	echo '<div class="centre">
			<input type="submit" name="Edit" value="' . _('Update Line') . '" />
		</div>
        </div>
		</form>';
	include('includes/footer.inc');
	exit;
}

	echo '<form name="form1" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?salescaseref=' . $salescaseref.'" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';
echo '<tr>
		<th colspan="2"><h4>' . _('Delivery Challan') . '</h4></th>
	</tr>
	<tr><td>Company</td>
		';


echo '<td><select  disabled = "disbaled" required="required" name="dba" onchange="ReloadForm(form1.UpdateCustomer)">';
if (!isset($_SESSION['dba'])) {
	$_SESSION['dba'] ='';
}
if ($_SESSION['dba'] == '') {
	echo '<option selected="selected" value="">' . _('Select dba') . '</option>';
} else {
	echo '<option value="">' . _('Select dba') . '</option>';
}	
$sql = "select * from dba";
$result=DB_query($sql, $db);
while ($myrow=DB_fetch_array($result)){
	if (isset($_SESSION['dba']) AND $_SESSION['dba']==$myrow['companyname']){
		echo '<option selected="True" value="' . $myrow['companyname'] . '">' . htmlspecialchars($myrow['companyname'], ENT_QUOTES,'UTF-8') . '</option>';
	} else {
		echo '<option value="' . $myrow['companyname'] . '">' . htmlspecialchars($myrow['companyname'], ENT_QUOTES,'UTF-8') . '</option>';
	}
}
echo '</select></td></tr>';

echo '
	<tr>
		<td>' . _('Date') . ':</td>';
echo '<td><input type="text" required = "required" class="date"  name="DispatchDate" maxlength="10" size="11" value="' . $_SESSION['Request']->DispatchDate . '" /></td>
      </tr>';
echo '<tr><td>' . _('Customer') .':</td>
	<td><select  disabled = "disbaled" name="Customer" onchange="ReloadForm(form1.UpdateCustomer)">';
$CustResult = DB_query("SELECT * FROM debtorsmaster where dba = '".$_SESSION['dba']."'",$db);
if (!isset($_SESSION['Customer'])) {
	$_SESSION['Customer'] ='';
}
if ($_SESSION['Customer'] == '') {
	echo '<option selected="selected" value="">' . _('Select Customer') . '</option>';
} else {
	echo '<option value="">' . _('Select Customer') . '</option>';
}	
while ($CustRow = DB_fetch_array($CustResult)){
	if ($CustRow['debtorno']==$_SESSION['Customer']){
		echo '<option selected="True" value="' . $CustRow['debtorno'] .'">' . $CustRow['name'] . '</option>';
	} else {
		echo '<option value="' . $CustRow['debtorno'] .'">' . $CustRow['name'] . '</option>';
	}
}
echo '</select></td></tr>';

echo '<tr><td>' . _('Customer branch') .':</td>
	<td><select  required="required" name="CustBranch" onchange="ReloadForm(form1.UpdateCustomer)">';
//	echo "SELECT * FROM custbranch where debtorno = '".$_SESSION['Customer']."'";
if (!isset($_SESSION['CustBranch'])) {
	$_SESSION['CustBranch'] ='';
}
if ($_SESSION['CustBranch'] == '') {
	echo '<option selected="selected" value="">' . _('Select Customer Branch') . '</option>';
} else {
	echo '<option value="">' . _('Select Customer Branch') . '</option>';
}	
$CustBranchResult = DB_query("SELECT * FROM custbranch where debtorno = '".$_SESSION['Customer']."'",$db);

while ($CustBranchRow = DB_fetch_array($CustBranchResult)){
	if ($_SESSION['CustBranch']==$CustBranchRow['branchcode']){
		echo '<option selected="True" value="' . $CustBranchRow['branchcode'] .'">' . $CustBranchRow['brname'] . '</option>';
	} else {
		echo '<option value="' . $CustBranchRow['branchcode'] .'">' . $CustBranchRow['brname'] . '</option>';
	}
}

echo '</select></td></tr>';
 $sql = "SELECT * from salescasecontacts inner join custcontacts
				on salescasecontacts.contid = custcontacts.contid
						where salescaseref = '".$salescaseref."'";
						$result=DB_query($sql,$db);
echo'<tr><td>' . _('Customer Contact') .':</td>	<td><select  required="required" name="CustContact" onchange="ReloadForm(form1.UpdateCustomer)">';
//	echo "SELECT * FROM custbranch where debtorno = '".$_SESSION['Customer']."'";
if (!isset($_SESSION['CustContact'])) {
	$_SESSION['CustContact'] ='';
}
if ($_SESSION['CustContact'] == '') {
	echo '<option selected="selected" value="">' . _('Select Customer Contact') . '</option>';
} else {
	echo '<option value="">' . _('Select Customer Contact') . '</option>';
}	

while ($CustContactRow = DB_fetch_array($result)){
	if ($_SESSION['CustContact']==$CustContactRow['contid']){
		echo '<option selected="True" value=' . $CustContactRow['contid'] .'>' . $CustContactRow['contactname'] . '</option>';
	} else {
		echo '<option value=' . $CustContactRow['contid'] .'>' . $CustContactRow['contactname'] . '</option>';
			}
}

echo '</select></td></tr>';

//if (isset($_POST['Submit']) OR isset($_POST['Update']) OR isset($_POST['Go'])OR isset($_POST['Edit']) OR isset($_POST['Next']) OR isset($_POST['Previous']) OR isset($_GET['Delete']))
//{
//echo "SELECT * FROM custbranch where branchcode = '".$_POST['CustBranch']."'";

$CustBranchResult = DB_query("SELECT * FROM custbranch where branchcode = '".$_SESSION['CustBranch']."'",$db);
$CustBranchRow = DB_fetch_array($CustBranchResult);

//}


$deliverto = $CustBranchRow['brname'].' '.$CustBranchRow['braddress1'];
$_SESSION['destination'] = $deliverto;


echo '

<tr>
		<td>' . _('Deliver to') . ':</td>
		<td><textarea required = "required" name="deliverto" cols="30" rows="5" 
		defaultvalue = 
		' . $deliverto.'
		> ' . $deliverto.'</textarea></td>
	</tr>';
	if (isset($_POST['CustBranch']))
		echo	'<td><input type = "hidden" name="destination" value = "' . $deliverto. '"></td>';
	else
		echo	'<td><input type = "hidden" name="destination" value = ""></td>';


	
echo'<tr>
		<td>' . _('P.O. No.') . ':</td>
		<td><input type = "text" name="po" value = "' . $_SESSION['Request']->po . '"></td>
	
</tr>
<tr>
		<td>' . _('TR/RR/Courier/Pickup No.') . ':</td>
		<td><input type = "text" name="ref" value = "' . $_SESSION['Request']->ref . '"></td>
	</tr>

	</table>';
echo '<input type="submit" name="UpdateCustomer" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />';

echo'	<br />';

echo '<div class="centre">
		<input type="submit" name="Update" value="' . _('Update') . '" />
	</div>
    </div>
	</form>';

if (!isset($_SESSION['Request']->Location)) {
	include('includes/footer.inc');
	exit;
}


$i = 0; //Line Item Array pointer
	echo '<form  enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?salescaseref=' . $salescaseref.'" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br />
	<table class="selection">
	<tr>
		<th colspan="7"><h4>' . _('Details of Items Requested') . '</h4></th>
	</tr>
	<tr>
		<th>' .  _('Line Number') . '</th>
		<th class="ascending">' .  _('Item Code') . '</th>
		<th class="ascending">' .  _('Item Description'). '</th>
		<th class="ascending">' .  _('Model No.'). '</th>
		<th class="ascending">' .  _('Quantity Required'). '</th>
		<th class="ascending">' .  _('Comments'). '</th>
		<th>' .  _('UOM'). '</th>
	</tr>';

$k=0;

foreach ($_SESSION['Request']->LineItems as $LineItems) {

	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k++;
	}
	echo '<td>' . $LineItems->LineNumber . '</td>
			<td>' . $LineItems->StockID . '</td>
			<td>' . $LineItems->ItemDescription . '</td>
			<td class="number">' . locale_number_format($LineItems->Quantity, $LineItems->DecimalPlaces) . '</td>
			<td>' . $LineItems->Comments . '</td>
			<td>' . $LineItems->UOM . '</td>
			<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit='.$LineItems->LineNumber.'&RequestNo=' . $row_result['dispatchid'] . '&salescaseref=' . $salescaseref .'">' . _('Edit') . '</a></td>
			<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete='.$LineItems->LineNumber.'&RequestNo=' . $row_result['dispatchid'] . '&salescaseref=' . $salescaseref .'">' . _('Delete') . '</a></td>
		</tr>';
}

echo '</table>';

echo'	<br />
	<div class="centre">
		<input type="submit" name="Submit" value="' . _('Submit') . '" />
	</div>
	<br />
    </div>
    </form>';

// query for list of record(s)
if(isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Edit']) OR isset($_POST['Previous'])) {
	$_POST['Update']='Update';
}
if (isset($_POST['Update']) OR isset($_POST['Go'])OR isset($_POST['Edit']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Update then set to first page
		$_POST['PageOffset'] = 1;
	}
		$SQL5 = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockissuance.issued AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN stockissuance
					ON stockmaster.stockid=stockissuance.stockid
					where stockissuance.salesperson = '".$_SESSION['Request']->salesperson."'
					and stockissuance.issued>0
					order by stockissuance.issued desc
					";
	
	/*if ($count > 0)
	$UpdateResult = DB_query($SQL2, $db, $ErrMsg, $DbgMsg);
	else
	$UpdateResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
	*/
	
	$UpdateResult = DB_query($SQL5, $db);
	
}
	
	//unset($_POST['Update']);


	if (isset($_POST['Next'])) {
		$Offset = $_POST['NextList'];
	}
	if (isset($_POST['Prev'])) {
		$Offset = $_POST['Previous'];
	}
	if (!isset($Offset) or $Offset<0) {
		$Offset=0;
	}
	 $SQL = $SQL5 . ' LIMIT ' . $DefaultDisplayRecordsMax . ' OFFSET ' . ($DefaultDisplayRecordsMax*$Offset);
		
	//$ErrMsg = _('There is a problem selecting the part records to display because');
	//$DbgMsg = _('The SQL used to get the part selection was');
	$UpdateResult = DB_query($SQL5,$db);

	if (DB_num_rows($UpdateResult)==0 ){
		prnMsg (_('There are no products available meeting the criteria specified'),'info');
	}
	if (DB_num_rows($UpdateResult)<$_SESSION['DisplayRecordsMax']){
		$Offset=0;
	}

 //end of if Update
/* display list if there is more than one record */
if (isset($Updateresult) AND !isset($_POST['Select'])) {
		echo '<form enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?salescaseref=' . $salescaseref.'" method="post">';
echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$ListCount = DB_num_rows($Updateresult);
	if ($ListCount > 0) {
		// If the user hit the Update button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
				} else {
					echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />
				<input type="hidden" name=Keywords value="'.$_POST['Keywords'].'" />
				<input type="hidden" name=StockCat value="'.$_POST['StockCat'].'" />
				<input type="hidden" name=StockCode value="'.$_POST['StockCode'].'" />
				<br />
				</div>';
		}
		echo '<table cellpadding="2">';
		echo '<tr>
				<th>' . _('Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Model No.') . '</th>
				<th>' . _('Total Qty On Hand') . '</th>
				<th>' . _('Units') . '</th>
				<th>' . _('Stock Status') . '</th>
			</tr>';
		$j = 1;
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
		if (DB_num_rows($Updateresult) <> 0) {
			DB_data_seek($Updateresult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($myrow = DB_fetch_array($Updateresult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			if ($myrow['mbflag'] == 'D') {
				$qoh = _('N/A');
			} else {
				$qoh = locale_number_format($myrow['qoh'], $myrow['decimalplaces']);
			}
			if ($myrow['discontinued']==1){
				$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
			} else {
				$ItemStatus ='';
			}

			echo '<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
					<td>' . $myrow['description'] . '</td>
					<td>' . $myrow['mnfCode'] . '</td>
					<td class="number">' . $qoh . '</td>
					<td>' . $myrow['units'] . '</td>
					<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'].'">' . _('View') . '</a></td>
					<td>' . $ItemStatus . '</td>
				</tr>';
			//end of page full new headings if
		}
		//end of while loop
		echo '</table>
              </div>
              </form>
              <br />';
	}
}
/* end display list if there is more than one record */

if (isset($UpdateResult)) {
	$j = 1;
	echo '<br />
		<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Order when ready.') . '</div>
		<br />
			<form name="SalesCaseEntry" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?salescaseref=' . $salescaseref.'" method="post">
<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table class="table1">
		<tr>
			<td>
				<input type="hidden" name="Previous" value="'.($Offset-1).'" />
				<input tabindex="'.($j+8).'" type="submit" name="Prev" value="'._('Prev').'" /></td>
				<td style="text-align:center" colspan="6">
				<input type="hidden" name="order_items" value="1" />
				<input tabindex="'.($j+9).'" type="submit" value="'._('Add to DC').'" /></td>
			<td>
				<input type="hidden" name="NextList" value="'.($Offset+1).'" />
				<input tabindex="'.($j+10).'" type="submit" name="Next" value="'._('Next').'" /></td>
			</tr>
			<tr>
				<th class="ascending">' . _('Code') . '</th>
				<th class="ascending">' . _('Description') . '</th>
				<th class="ascending">' . _('Model No.') . '</th>
				
				<th>' . _('Units') . '</th>
				<th class="ascending">' . _('On Hand') . '</th>
				
				<th class="ascending">' . _('Quantity') . '</th>
				<th class="ascending">' . _(' ') . '</th>
			</tr>';
	$ImageSource = _('No Image');

	$k=0; //row colour counter
	$i=0;
	$QOH = array();
	while ($myrow=DB_fetch_array($UpdateResult)) {
		if ($myrow['decimalplaces']=='') {
			$DecimalPlacesSQL="SELECT decimalplaces
								FROM stockmaster
								WHERE stockid='" .$myrow['stockid'] . "'";
			$DecimalPlacesResult = DB_query($DecimalPlacesSQL, $db);
			$DecimalPlacesRow = DB_fetch_array($DecimalPlacesResult);
			$DecimalPlaces = $DecimalPlacesRow['decimalplaces'];
		} else {
			$DecimalPlaces=$myrow['decimalplaces'];
		}

		$QOHSQL = "SELECT issued AS qoh
							   FROM stockissuance
							   WHERE stockissuance.stockid='" .$myrow['stockid'] . "' AND
							   stockissuance.salesperson = '" . $_SESSION['Request']->salesperson . "'";
		$QOHResult =  DB_query($QOHSQL,$db);
		$QOHRow = DB_fetch_array($QOHResult);
		$q = 'Quantity'.$i;
		$p = 'ItemDescription'.$i;
		$QOH[$i] = $QOHRow['qoh'];
		for ($foo = 0; $foo <= count($_SESSION['Request']->LineItems); $foo++) {
			
			//echo $_SESSION['Request']->LineItems[$Index]->StockID;
			if ($_SESSION['Request']->LineItems[$foo]->ItemDescription == $_POST[$p])
			{
					$QOH[$i] = $QOHRow['qoh'] -$_SESSION['Request']->LineItems[$foo]->Quantity - $_POST[$q] ;
			}
			
		}
		
			$QOH[$i] = $QOH[$i] - $_POST[$q] ;
			
			
		

		// Find the quantity on outstanding sales orders
		 

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$OnOrder = $PurchQty + $WoQty;
		$Available = $QOH[$i] - $DemandQty + $OnOrder;
		echo '<td><a href = "SelectProduct.php?Select=' . $myrow['stockid'] . '" target = "_blank" >' . $myrow['stockid'] . '</a></td>
				
				<td>' . $myrow['description'] . '</td>
				<td>' . $myrow['mnfCode'] . '</td>
				
				<td>' . $myrow['stockunits'] . '</td>
				<td class="number">' . locale_number_format($QOH[$i],$DecimalPlaces) . '</td>
			<td><input class="number" ' . ($i==0 ? 'autofocus="autofocus"':'') . ' tabindex="'.($j+7).'" type="text" size="6" name="Quantity'.$i.'" value="0" />
				<td><input  tabindex="'.($j+7).'" type="text" size="26" name="Comments'.$i.'" value=" " />
				
				
				<input type="hidden" name="StockID'.$i.'" value="'.$myrow['stockid'].'" />
				</td>
			</tr>';
		echo '<input type="hidden" name="DecimalPlaces'.$i.'" value="' . $myrow['decimalplaces'] . '" />';
		echo '<input type="hidden" name="ItemDescription'.$i.'" value="' . $myrow['description'] . '" />';
		echo '<input type="hidden" name="Units'.$i.'" value="' . $myrow['stockunits'] . '" />';
		$i++;
	}
#end of while loop
	echo '<tr>
			<td><input type="hidden" name="Previous" value="'.($Offset-1).'" />
				<input tabindex="'.($j+7).'" type="submit" name="Prev" value="'._('Prev').'" /></td>
			<td style="text-align:center" colspan="6"><input type="hidden" name="order_items" value="1" />
				<input tabindex="'.($j+8).'" type="submit" value="'._('Add to DC').'" /></td>
			<td><input type="hidden" name="NextList" value="'.($Offset+1).'" />
				<input tabindex="'.($j+9).'" type="submit" name="Next" value="'._('Next').'" /></td>
		<tr/>
		</table>
       </div>
       </form>';
}#end if UpdateResults to show

//*********************************************************************************************************
include('includes/footer.inc');
?>
