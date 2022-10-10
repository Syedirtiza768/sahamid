<?php

/* $Id: InternalStockRequest.php 4576 2011-05-27 10:59:20Z daintree $*/

include('includes/DefineDCClass.php');

include('includes/session.inc');
$Title = _('Create a Delivery Challan');
$ViewTopic = 'Inventory';
$BookMark = 'CreateRequest';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
if (isset($_GET['tranno']))
$_SESSION['tranno'] = $_GET['tranno'];
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

if (isset($_GET['tranno'])) {
	unset($_SESSION['Transfer']);
	$_SESSION['Request'] = new StockRequest();
}

if (isset($_GET['tranno'])) {
	$InputError=0;
	
	
	if ($InputError==0) {
		echo $SQL = 'SELECT * from dcclient where dispatchid = '.$_SESSION['tranno'].'';
 
$Result  = DB_query($SQL,$db);
$myrow = DB_fetch_array($Result);
		$_SESSION['Request']->Location=$_SESSION['UserStockLocation'];
		
		
		$_SESSION['Request']->DispatchDate=$myrow['despatchdate'];
		$_SESSION['Request']->Narrative=$myrow['narrative'];
		$_SESSION['Request']->substore=$myrow['substore'];
		$_SESSION['Request']->deliverto=$myrow['deliverto'];
		$_SESSION['Request']->salesperson=$myrow['salesperson']; 
		$_SESSION['Request']->po=$myrow['po']; 
		$_SESSION['Request']->ref=$myrow['ref']; 
		$_SESSION['Request']->gstclause=$myrow['gstclause']; 
		$_SESSION['Request']->dctype=$myrow['dctype']; 
		$_SESSION['Request']->rate=$myrow['rate']; 
		
		$_SESSION['Request']->dba=$myrow['dba']; 
		
		$_SESSION['Request']->storemanager=$_SESSION['UsersRealName']; 
		
	}
	
}
if (isset($_POST['Update'])) {
	$InputError=0;
	
	
	if ($InputError==0) {
		$_SESSION['Request']->Location=$_SESSION['UserStockLocation'];
		
		
		if(isset ($_POST['DispatchDate']))
		{	
	echo 'dated :'.$_SESSION['Request']->DispatchDate=$_POST['DispatchDate'];
		}
	$_SESSION['Request']->Narrative=$_POST['narrative'];
		$_SESSION['Request']->substore=$_POST['substore'];
		$_SESSION['Request']->deliverto=$_POST['deliverto'];
		//$_SESSION['Request']->salesperson=$_SESSION['UsersRealName']; 
		$_SESSION['Request']->po=$_POST['po']; 
		$_SESSION['Request']->ref=$_POST['ref']; 
		$_SESSION['Request']->gstclause=$_POST['gstclause']; 
		$_SESSION['Request']->dctype=$_POST['dctype']; 
		$_SESSION['Request']->rate=$_POST['rate']; 
		$_SESSION['Request']->dba=$_POST['dba']; 
		
		$_SESSION['Request']->storemanager=$_SESSION['UsersRealName']; 
		
	}
}
if (isset($_POST['Edit'])) {
	$_SESSION['Request']->LineItems[$_POST['LineNumber']]->ItemDescription=$_POST['ItemDescription'];
	
	$_SESSION['Request']->LineItems[$_POST['LineNumber']]->Quantity=$_POST['Quantity'];
	$_SESSION['Request']->LineItems[$_POST['LineNumber']]->rate=$_POST['rate'];
	
}

if (isset($_GET['Delete'])) {
	unset($_SESSION['Request']->LineItems[$_GET['Delete']]);
	echo $SQL = 'delete from dcclientitems where dispatchid = '.$_SESSION['tranno'].'

	and dispatchitemsid = '.$_GET['Delete'].'';
 
$Result  = DB_query($SQL,$db);
	echo '<br />';
	prnMsg( _('The line was successfully deleted'), 'success');
	echo '<br />';

	}
echo $SQL = 'SELECT * from dcclientitems where dispatchid = '.$_SESSION['tranno'].'';
 
$Result  = DB_query($SQL,$db);

if (isset($_GET['tranno']))

while ($myrow = DB_fetch_array($Result))
{
	$StockID=$myrow['stockid'];
			$ItemDescription=$myrow['description'];
			$DecimalPlaces=$myrow['decimalplaces'];
			$NewItem_array[$StockID] = filter_number_format($myrow['quantity']);
			
			$NewComment_array[$StockID] =$myrow['rate'];
			
			$_POST['Units'.$StockID]=$_POST['Units'.$Index];
			
			
			$_SESSION['Request']->AddLine($StockID, $ItemDescription, $NewItem_array[$StockID],$NewComment_array, $_POST['Units'.$StockID], $DecimalPlaces);
	
	$_SESSION['count']++;
	
	
	
}
	
foreach ($_POST as $key => $value) {
	if (mb_strstr($key,'StockID')) {
		$Index=mb_substr($key, 7);
		if (filter_number_format($_POST['Quantity'.$Index])>0) {
			$StockID=$value;
			$ItemDescription=$_POST['ItemDescription'.$Index];
			$DecimalPlaces=$_POST['DecimalPlaces'.$Index];
			$NewItem_array[$StockID] = filter_number_format($_POST['Quantity'.$Index]);
			
			$NewComment_array[$StockID] =$_POST['rate'.$Index];
			
			$_POST['Units'.$StockID]=$_POST['Units'.$Index];
			$_SESSION['Request']->AddLine($StockID, $ItemDescription, $NewItem_array[$StockID],$NewComment_array[$StockID], $_POST['Units'.$StockID], $DecimalPlaces);
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
		
		echo $HeaderSQL="update dcclient set 
											
											

											
											
											
											loccode = '" . $_SESSION['Request']->Location . "',
											po = '" . $_SESSION['Request']->po . "',
											ref = '" . $_SESSION['Request']->ref . "',
											
											dba = '" . $_SESSION['Request']->dba . "',
											gstclause = '" . $_SESSION['Request']->gstclause . "',
											dctype = '" . $_SESSION['Request']->dctype . "',
											despatchdate = '" . $_SESSION['Request']->DispatchDate . "',
											
											salesperson = '" . $_SESSION['Request']->salesperson . "',
											narrative = '" . $_SESSION['Request']->narrative . "',
											deliverto = '" . $_SESSION['Request']->deliverto . "'
											
											
											
										where dispatchid = 
											" .$_SESSION['tranno']; 
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);

		foreach ($_SESSION['Request']->LineItems as $LineItems) {
		echo $LineSQL="update dcclientitems set 
													
													stockid = '".$LineItems->StockID."',
													description = '".$LineItems->ItemDescription."',
													quantity = '".$LineItems->Quantity."',
													
													rate = '".$LineItems->rate."',
													decimalplaces = '".$LineItems->DecimalPlaces."',
													uom = '".$LineItems->UOM."'where 
													dispatchitemsid = '".$LineItems->LineNumber."'
													and dispatchid = ".$_SESSION['tranno']."";
													
													
													
													
													
													
													
													
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request line record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($LineSQL,$db);
		
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
		
		}
	}
	DB_Txn_Commit($db);
		echo '<p><a href="'.$RootPath.'/PDFDC.php?RequestNo=' . $_SESSION['tranno']. '">' .  _('Print the DC'). '</a></p>';
	echo '<p><a href="'.$RootPath.'/PDFDCCLIENT.php?RequestNo=' . $_SESSION['tranno']. '">' .  _('Print the DC for Client'). '</a></p>';
	
	include('includes/footer.inc');
	unset($_SESSION['Request']);
	exit;
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Dispatch') .
		'" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['Edit'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST">';
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
			<td><textarea name="ItemDescription" value="' . $_SESSION['Request']->LineItems[$_GET['Edit']]->ItemDescription. '" cols="30" rows="5" ></textarea></td>
		</tr>
		<tr>
			<td>' . _('Unit of Measure') . '</td>
			<td>' . $_SESSION['Request']->LineItems[$_GET['Edit']]->UOM . '</td>
		</tr>
		<tr>
			<td>' . _('Quantity Requested') . '</td>
			<td><input type="text" class="number" name="Quantity" value="' . locale_number_format($_SESSION['Request']->LineItems[$_GET['Edit']]->Quantity, $_SESSION['Request']->LineItems[$_GET['Edit']]->DecimalPlaces) . '" /></td>
			<td>' . _('rate') . '</td>
			<td><input type="text" name="rate" value="' . $_SESSION['Request']->LineItems[$_GET['Edit']]->rate. '" /></td>
		
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

echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';
echo '<tr>
		<th colspan="2"><h4>' . _('Delivery Challan') . '</h4></th>
	</tr>
	<tr><td>Company</td>
		';
$sql = "select * from dba";
$result=DB_query($sql, $db);
echo '<td><select name="dba">';
while ($myrow=DB_fetch_array($result)){
	if (isset($_SESSION['Request']->dba) AND $_SESSION['Request']->dba==$myrow['companyname']){
		echo '<option selected="True" value="' . $myrow['companyname'] . '">' . htmlspecialchars($myrow['companyname'], ENT_QUOTES,'UTF-8') . '</option>';
	} else {
		echo '<option value="' . $myrow['companyname'] . '">' . htmlspecialchars($myrow['companyname'], ENT_QUOTES,'UTF-8') . '</option>';
	}
}
echo '</select></td></tr>';
echo'	<tr><td> DC TYPE </td><td><select name="dctype">';
if (!isset($_POST['dctype'])){
	$_GET['dctype']='';
}
if ($_POST['dctype']=='Bill'){
	echo '
          <option selected="selected" value="Bill">' . _('Bill') . '</option>
          <option value="Delivery Challan">' . _('DC') . '</option>';
}
 else {
	echo '
          <option  value="Bill">' . _('Bill') . '</option>
          <option selected="selected" value="Delivery Challan">' . _('DC') . '</option>';
}
echo '</select></td></tr>';

echo'	<tr><td> GST Clause </td><td><select name="gstclause">';
if (!isset($_POST['gstclause'])){
	$_GET['gstclause']='';
}
if ($_POST['gstclause']==''){
	echo '<option selected="selected" value="">' . _('None') . '</option>
          <option value="Prices Inclusive of GST">' . _('Prices Inclusive of GST') . '</option>
          <option value="Prices Exclusive of GST">' . _('Prices Exclusive of GST') . '</option>';
} else if ($_POST['gstclause']=='Prices Inclusive of GST'){
	echo '<option  value="">' . _('None') . '</option>
          <option selected="selected" value="Prices Inclusive of GST">' . _('Prices Inclusive of GST') . '</option>
          <option value="Prices Exclusive of GST">' . _('Prices Exclusive of GST') . '</option>';
}
 else if ($_POST['gstclause']=='Prices Exclusive of GST'){
	echo '<option  value="">' . _('None') . '</option>
          <option value="Prices Inclusive of GST">' . _('Prices Inclusive of GST') . '</option>
          <option selected="selected" value="Prices Exclusive of GST">' . _('Prices Exclusive of GST') . '</option>';
}
echo '</select></td></tr>';

echo '
	<tr>
		<td>' . _('Date') . ':</td>';
echo '<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="DispatchDate" maxlength="10" size="11" value="' . $_SESSION['Request']->DispatchDate . '" /></td>
      </tr>';

echo '

<tr>
		<td>' . _('Deliver to *') . ':</td>
		<td><textarea required = "required" name="deliverto" cols="30" rows="5">' . $_SESSION['Request']->deliverto . '</textarea></td>
	</tr>
<tr>
		<td>' . _('Narrative') . ':</td>
		<td><textarea name="narrative" cols="30" rows="5">' . $_SESSION['Request']->Narrative . '</textarea></td>
	</tr>

	<tr>
		<td>' . _('P.O. No.') . ':</td>
		<td><input type = "text" name="po" value = "' . $_SESSION['Request']->po . '"></td>
	
</tr>
<tr>
		<td>' . _('TR/RR/Courier/Pickup No.') . ':</td>
		<td><input type = "text" name="ref" value = "' . $_SESSION['Request']->ref . '"></td>
	</tr>

	</table>
	<br />';

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
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST">';
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
		<th class="ascending">' .  _('Quantity Required'). '</th>
		<th class="ascending">' .  _('rate'). '</th>
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
			<td>' . $LineItems->rate . '</td>
			<td>' . $LineItems->UOM . '</td>
			<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit='.$LineItems->LineNumber.'">' . _('Edit') . '</a></td>
			<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete='.$LineItems->LineNumber.'">' . _('Delete') . '</a></td>
		</tr>';
}

echo '</table>
	<br />
	<div class="centre">
	<br>
	
	<input type = "checkbox" required = "required" > Form Rechecked
	</br>
		<input type="submit" name="Submit" value="' . _('Submit') . '" />
	</div>
	<br />
    </div>
    </form>';

// query for list of record(s)
#end if UpdateResults to show

//*********************************************************************************************************
include('includes/footer.inc');
?>
