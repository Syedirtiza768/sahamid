<?php

/* $Id: InternalStockRequest.php 4576 2011-05-27 10:59:20Z daintree $*/

include('includes/DefineStockRequestClass.php');

include('includes/session.inc');
$Title = _('Create an InterStore Materials Request');
$ViewTopic = 'Inventory';
$BookMark = 'CreateRequest';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$DefaultDisplayRecordsMax = 100;
if (isset($_POST['brand']))
	$_SESSION['brand'] = $_POST['brand'];

if (isset($_POST['stockrequest'])) {
	$_SESSION["salescaseref"] = $_POST['stockrequest'];
}

//else
//$_SESSION['brand'] = 'All';
if (isset($_POST['StockID'])) {
	//The page is called with a StockID
	$_POST['StockID'] = trim(mb_strtoupper($_POST['StockID']));
	$_POST['Select'] = trim(mb_strtoupper($_POST['StockID']));
}

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Inventory Items') . '" alt="" />' . ' ' . _('Inventory Items') . '</p>';
if (isset($_POST['NewSearch']) or isset($_POST['Next']) or isset($_POST['Previous']) or isset($_POST['Go'])) {
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
if (isset($_POST['StockCode'])) {
	$_POST['StockCode'] = trim(mb_strtoupper($_POST['StockCode']));
}
// Always show the search facilities
error_reporting(0);
$SQL = "SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$result1 = DB_query($SQL, $db);
if (isset($_POST['UpdateCategories'])) {

	if ($_POST['StockCat'] == 'All') {
		$SQLManufacturers = "SELECT * from manufacturers";
	} else {
		$SQLManufacturers = "SELECT distinct manufacturers_id,
				manufacturers_name
		FROM manufacturers, stockmaster, stockcategory
		where stockmaster.brand = manufacturers.manufacturers_id
		and stockmaster.categoryid = stockcategory.categoryid
		and stockcategory.categoryid = " . "'" . $_POST['StockCat'] . "'" . "
		ORDER BY manufacturers_name";
	}
	$result2 = DB_query($SQLManufacturers, $db);
} else {
	$SQLManufacturers = "select * from manufacturers";
	$result2 = DB_query($SQLManufacturers, $db);
}
if (DB_num_rows($result1) == 0) {
	echo '<p class="bad">' . _('Problem Report') . ':<br />' . _('There are no stock categories currently defined please use the link below to set them up') . '</p>';
	echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
	exit;
}
if (DB_num_rows($result2) == 0) {
	echo '<p class="bad">' . _('Problem Report') . ':<br />' . _('Items not inserted in this category') . '</p>';
}

if (isset($_GET['New'])) {
	unset($_SESSION['Transfer']);
	$_SESSION['Request'] = new StockRequest();
}

if (isset($_POST['Update'])) {
	$InputError = 0;

	if ($_POST['Location'] == '') {
		prnMsg(_('You must select a Location to request the items from'), 'error');
		$InputError = 1;
	}
	if ($InputError == 0) {
		$_SESSION['Request']->SourceLocation = $_POST['Location'];
		$_SESSION['Request']->RequestDate = $_POST['RequestDate'];
		$_SESSION['Request']->Narrative = $_POST['Narrative'];
		$SQLA = "select defaultlocation from www_users where www_users.realname = '" . $_SESSION['UsersRealName'] . "'";
		$result = DB_query($SQLA, $db);
		$myrow = DB_fetch_array($result);
		$_SESSION['Request']->DestinationLocation = $myrow['defaultlocation'];

		$_SESSION['Request']->salesperson = $_SESSION['UsersRealName'];
	}
}

if (isset($_POST['Edit'])) {
	$_SESSION['Request']->LineItems[$_POST['LineNumber']]->Quantity = $_POST['Quantity'];
	$_SESSION['Request']->LineItems[$_POST['LineNumber']]->Comments = $_POST['Comments'];
}

if (isset($_GET['Delete'])) {
	unset($_SESSION['Request']->LineItems[$_GET['Delete']]);
	echo '<br />';
	prnMsg(_('The line was successfully deleted'), 'success');
	echo '<br />';
}

foreach ($_POST as $key => $value) {
	if (mb_strstr($key, 'StockID')) {
		$Index = mb_substr($key, 7);
		if (filter_number_format($_POST['Quantity' . $Index]) > 0) {
			$StockID = $value;
			$ItemDescription = $_POST['ItemDescription' . $Index];
			$DecimalPlaces = $_POST['DecimalPlaces' . $Index];
			$NewItem_array[$StockID] = filter_number_format($_POST['Quantity' . $Index]);
			$NewComment_array[$StockID] = $_POST['Comments' . $Index];

			$_POST['Units' . $StockID] = $_POST['Units' . $Index];
			$_SESSION['Request']->AddLine($StockID, $ItemDescription, $NewItem_array[$StockID], $NewComment_array[$StockID], $_POST['Units' . $StockID], $DecimalPlaces);
		}
	}
}
$count = 0;
foreach ($_SESSION['Request']->LineItems as $LineItems) {

	$count++;
}





if (isset($_POST['Submit']) and $count > 0) {
	DB_Txn_Begin($db);
	$InputError = 0;

	if ($_SESSION['Request']->SourceLocation == '') {
		prnMsg(_('You must select a Location to request the items from'), 'error');
		$InputError = 1;
	}
	if ($InputError == 0) {
		$RequestNo = GetNextTransNo(38, $db);

				$HeaderSQL = "INSERT INTO stockrequest (dispatchid,
											requestdate,
											salesperson,
											recloc,
											shiploc,
											narrative)
										VALUES(
											'" . $RequestNo . "',
											'" . $_SESSION['Request']->RequestDate . "',
											'" . $_SESSION['Request']->salesperson . "',
											'" . $_SESSION['Request']->DestinationLocation . "',
											
											'" . $_SESSION['Request']->SourceLocation . "',
											'" . $_SESSION['Request']->Narrative . "')";
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);

		$sql = "UPDATE stockrequest
					SET authorised='1',authorizer = '" . $_SESSION['UsersRealName'] . "'
					WHERE dispatchid='" . $RequestNo . "'";
		$result = DB_query($sql, $db);

		foreach ($_SESSION['Request']->LineItems as $LineItems) {
			$LineSQL = "INSERT INTO stockrequestitems (dispatchitemsid,
													dispatchid,
													stockid,
													quantity,
													comments,
													decimalplaces,
													uom)
												VALUES(
													'" . $LineItems->LineNumber . "',
													'" . $RequestNo . "',
													'" . $LineItems->StockID . "',
													'" . $LineItems->Quantity . "',
													'" . $LineItems->Comments . "',
													'" . $LineItems->DecimalPlaces . "',
													'" . $LineItems->UOM . "')";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request line record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($LineSQL, $db, $ErrMsg, $DbgMsg, true);
		}

		$selectedItemsCode = NULL;
		foreach ($_SESSION['Request']->LineItems as $LineItems) {
			$itemcode = "SELECT * FROM ogpsalescaseref WHERE salescaseref= '" . $_SESSION["salescaseref"] . "'";
			$Result = DB_query($itemcode, $db);
	
			$HeaderSalescaserefSQL = "INSERT INTO ogpsalescaseref (dispatchid,
												salescaseref,
												requestedby,
												stockid
												)
											VALUES (
												'" . $RequestNo . "',
												'" . $_SESSION["salescaseref"] . "',
												'" . $_SESSION['UsersRealName'] . "',
												'" . $LineItems->StockID . "')";
	
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the request header record was used');
			$Result = DB_query($HeaderSalescaserefSQL, $db, $ErrMsg, $DbgMsg, true);
		}

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
	DB_Txn_Commit($db);
	prnMsg(_('The internal stock request has been entered and waiting to be fulfilled by store manager'), 'success');
	echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?New=Yes">' . _('Create another request') . '</a></div>';
	include('includes/footer.inc');
	unset($_SESSION['Request']);
	exit;
}

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Dispatch') .
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
			<td><input type="text" name="Comments" value="' . $_SESSION['Request']->LineItems[$_GET['Edit']]->Comments . '" /></td>
		
		</tr>';
	echo '<input type="hidden" name="LineNumber" value="' . $_SESSION['Request']->LineItems[$_GET['Edit']]->LineNumber . '" />';
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

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';
echo '<tr>
		<th colspan="2"><h4>' . _('Internal Stock Request Details') . '</h4></th>
	</tr>
	<tr>
		<td>' . _('Location from which to request stock') . ':</td>';
$sql = "SELECT loccode,
			locationname
		FROM locations
		WHERE internalrequest = 1
		ORDER BY locationname";

$result = DB_query($sql, $db);
echo '<td><select name="Location">
		<option value="">' . _('Select a Location') . '</option>';
while ($myrow = DB_fetch_array($result)) {
	if (isset($_SESSION['Request']->SourceLocation) and $_SESSION['Request']->SourceLocation == $myrow['loccode']) {
		echo '<option selected="True" value="' . $myrow['loccode'] . '">' . $myrow['loccode'] . ' - ' . htmlspecialchars($myrow['locationname'], ENT_QUOTES, 'UTF-8') . '</option>';
	} else {
		echo '<option value="' . $myrow['loccode'] . '">' . $myrow['loccode'] . ' - ' . htmlspecialchars($myrow['locationname'], ENT_QUOTES, 'UTF-8') . '</option>';
	}
}
echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Salescase Referenece') . ':</td>';
echo '<td><input type="text" class="date" alt="' . $_SESSION["salescaseref"] . '" name="RequestDate" size="11"  style="width:190px" readonly value="' . $_SESSION["salescaseref"] . '" /></td>
      </tr>';

echo '<tr>
		<td>' . _('Date when required') . ':</td>';
echo '<td><input type="date" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="RequestDate" maxlength="10" size="11" value="' . $_SESSION['Request']->RequestDate . '" /></td>
      </tr>';

echo '<tr>
		<td>' . _('Narrative') . ':</td>
		<td><textarea name="Narrative" cols="30" rows="5">' . $_SESSION['Request']->Narrative . '</textarea></td>
	</tr>
	</table>
	<br />';

echo '<div class="centre">
		<input type="submit" name="Update" value="' . _('Update') . '" />
	</div>
    </div>
	</form>';

if (!isset($_SESSION['Request']->SourceLocation)) {
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
		<th class="ascending">' .  _('Item Description') . '</th>
		<th class="ascending">' .  _('Quantity Required') . '</th>
		<th class="ascending">' .  _('Comments') . '</th>
		<th>' .  _('UOM') . '</th>
	</tr>';

$k = 0;

foreach ($_SESSION['Request']->LineItems as $LineItems) {

	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
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
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit=' . $LineItems->LineNumber . '">' . _('Edit') . '</a></td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete=' . $LineItems->LineNumber . '">' . _('Delete') . '</a></td>
		</tr>';
}

echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="Submit" value="' . _('Submit') . '" />
	</div>
	<br />
    </div>
    </form>';
echo '<form name = "searchform" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Inventory Items') . '</p>';
echo '<table class="selection"><tr>';
echo '<td>' . _('In Stock Category') . ':';
echo '<select name="StockCat" onchange="ReloadForm(searchform.UpdateCategories)">';
if (!isset($_POST['StockCat'])) {
	$_POST['StockCat'] = 'All';
}
if ($_POST['StockCat'] == 'All') {
	echo '<option selected="selected" value="All">' . _('All') . '</option>';
} else {
	echo '<option value="All">' . _('All') . '</option>';
}
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	}
}
echo '</select></td>';
echo '<td>' . _('Brand') . ':';
echo '<select name="brand">';



/*
if (!isset($_POST['brand'])) {
	$_POST['brand'] ='';
}
*/

if ($_POST['brand'] == 'All') {

	echo '<option selected="selected" value="All">' . _('All') . '</option>';
} else {
	echo '<option value="All">' . _('All') . '</option>';
}

while ($myrow2 = DB_fetch_array($result2)) {
	if ($myrow2['manufacturers_id'] == $_SESSION['brand']) {
		echo '<option selected="selected" value="' . $myrow1['manufacturers_id'] . '">' . $myrow2['manufacturers_name'] . '</option>';
	} else {
		echo '<option value="' . $myrow2['manufacturers_id'] . '">' . $myrow2['manufacturers_name'] . '</option>';
	}
}
echo '</select></td>';
/*
echo '<td>' . _('Enter partial') . '<b> ' . _('Description') . '</b>:</td><td>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" autofocus="autofocus" name="Keywords" value="' . $_POST['Keywords'] . '" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
} else {
	echo '<input type="text" autofocus="autofocus" name="Keywords" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
}
*/

echo '<td><b>' . _('') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Stock Code/Part number/Manufacturer Code') . '</b>:</td>';

echo '<td>';

if (isset($_POST['StockCode'])) {
	echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
} else {
	echo '<input type="text" name="StockCode" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
}

echo '</tr></table><br />';
if (isset($_POST['StockCat']) and $_POST['StockCat'] != 'All') {
	$SQL = 'SELECT * FROM `stockcatproperties` WHERE categoryid like "' . $_POST['StockCat'] . '"';
	$result3 = DB_query($SQL, $db);
	echo '<table> <tr><td><b>Property Name</b></td><td><b>Value</b></td></tr>


';
	while ($myrow3 = DB_fetch_array($result3)) {
		echo '<tr><td>' . $myrow3['label'] . '</td>';

		if ($myrow3['controltype'] == 1) {
			$OptionValues = explode(',', $myrow3['defaultvalue']);
			echo '<td><select name="PropValue[]" size = "1">';
			echo '<option value="">' . _('All') . '</option>';

			foreach ($OptionValues as $PropertyOptionValue) {
				if ($PropertyOptionValue == $PropertyValue) {

					echo '<option selected="selected" value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
				} else {
					echo '<option value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
				}
			}
			echo '</select></td></tr>';
		}


		if ($myrow3['controltype'] == 0 and ($myrow3['minimumvalue'] != 0 or $myrow3['maximumvalue'] != 0))
		//echo '<tr><td>'.$myrow3['label'].'</td><td>'.$myrow3['minimumvalue']."<=".'<input type = "text" name = "PropValue[]">'.'<= '.$myrow3['maximumvalue'].'</td>';
		{

			echo '<td>Value Range ' . $myrow3['minimumvalue'] . ' to ' . $myrow3['maximumvalue'] . '
<input name="PropValueMin[]"type = "text"><= x <= <input name="PropValueMax[]"type = "text"></td></tr>';
		}
		if ($myrow3['controltype'] == 0 and ($myrow3['minimumvalue'] == 0 and $myrow3['maximumvalue'] == 0))
		//echo '<tr><td>'.$myrow3['label'].'</td><td>'.$myrow3['minimumvalue']."<=".'<input type = "text" name = "PropValue[]">'.'<= '.$myrow3['maximumvalue'].'</td>';
		{
			echo '<td><input name="PropValueText[]" type = "text"></td></tr>';
		}
	}
	echo '</table>';
}
echo '<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />';

echo '<div class="centre"><input type="submit" name="Search" value="' . _('Search Now') . '" /></div><br />';
echo '</div>
      </form>';
// query for list of record(s)
if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev'])) {


	if (isset($_POST['StockCode'])) {
		//insert wildcard characters in spaces
		$SearchString2 = '%' . str_replace(' ', '%', $_POST['StockCode']) . '%';
		if ($_POST['StockCat'] == 'All') {
			if ($_SESSION['brand'] == 'All') {
				$SQLA = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.mbflag,
						stockmaster.discontinued,
						locstock.quantity AS qohand,
						stockmaster.units,
						stockmaster.decimalplaces
						FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
						WHERE (stockmaster.mnfCode " . LIKE . " '%" . $SearchString2 . "%'
					or stockmaster.stockid " . LIKE . " '%" . $SearchString2 . "%')
					and locstock.loccode = '" . $_SESSION['Request']->SourceLocation . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY locstock.quantity desc";
			} else
				$SQLA = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.mbflag,
						stockmaster.discontinued,
						locstock.quantity AS qohand,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid=locstock.stockid
					WHERE brand ='" . $_SESSION['brand'] . "'
					and locstock.loccode = '" . $_SESSION['Request']->SourceLocation . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY locstock.quantity desc";
		} else {
			if ($_SESSION['brand'] == 'All') {
				$SQLA = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.mbflag,
						stockmaster.discontinued,
						locstock.quantity AS qohand,
						stockmaster.units,
						stockmaster.decimalplaces
						FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
						WHERE 
						categoryid='" . $_POST['StockCat'] . "'
						and locstock.loccode = '" . $_SESSION['Request']->SourceLocation . "'
						AND stockmaster.stockid NOT LIKE '%\t%'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY locstock.quantity desc";
			} else {

				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.mbflag,
						stockmaster.discontinued,
						locstock.quantity AS qohand,
						stockmaster.units,
						stockmaster.decimalplaces
						FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
						WHERE brand='" . $_SESSION['brand'] . "'
						AND categoryid='" . $_POST['StockCat'] . "'
						and locstock.loccode = '" . $_SESSION['Request']->SourceLocation . "'
					
						AND stockmaster.stockid NOT LIKE '%\t%'
					GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY locstock.quantity desc";
			}
		}
	} elseif (isset($_POST['StockCode'])) {
		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);

		$SQLA = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.mbflag,
						stockmaster.discontinued,
						locstock.quantity AS qohand,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid=locstock.stockid
					WHERE (stockmaster.stockid " . LIKE . " '%" . $SearchString2 . "%'
					or stockmaster.mnfCode " . LIKE . " '%" . $SearchString2 . "%')
					and locstock.loccode = '" . $_SESSION['Request']->SourceLocation . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY locstock.quantity desc";
	}
} elseif (!isset($_POST['StockCode']) and !isset($_POST['keyproperties']) and isset($_POST['Search'])) {
	if ($_POST['StockCat'] == 'All') {
		$SQLA = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.mbflag,
						stockmaster.discontinued,
						locstock.quantity AS qohand,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid=locstock.stockid
					WHERE brand='" . $_SESSION['brand'] . "'
					and locstock.loccode = '" . $_SESSION['Request']->SourceLocation . "'
					
					AND stockmaster.stockid NOT LIKE '%\t%'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY locstock.quantity desc";
	} else {
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.mbflag,
						stockmaster.discontinued,
						locstock.quantity AS qohand,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid=locstock.stockid
					WHERE categoryid like'" . $_POST['StockCat'] . "'
					AND brand like '" . $_SESSION['brand'] . "'
					and locstock.loccode = '" . $_SESSION['Request']->SourceLocation . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY locstock.quantity desc";
	}
}

$count = 0;

if (isset($_POST['StockCat']) and $_POST['StockCat'] != 'All') {
	foreach ($_POST['PropValue'] as $val) {
		if ($val != '')
			$count++;
	}
}
//echo $count."<br>";

$SQL2 = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.mbflag,
						stockmaster.discontinued,
						locstock.quantity AS qohand,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid=locstock.stockid inner join stockitemproperties
					on stockmaster.stockid = stockitemproperties.stockid
					where locstock.loccode = '" . $_SESSION['Request']->SourceLocation . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
						and stockmaster.categoryid like'" . $_POST['StockCat'] . "'
					and( ";
if (isset($_POST['StockCat']) and $_POST['StockCat'] != 'All') {
	$count = 0;
	foreach ($_POST['PropValue'] as $value) {
		if ($value != '') {
			$count++;
			if ($count <= 1)
				$SQL2 .= "		stockitemproperties.value like '" . $value . "'";
			else
				$SQL2 .= "		or stockitemproperties.value like '" . $value . "'";
		}
	}
}
$SQL2 .= "";

if (isset($_POST['StockCat']) and $_POST['StockCat'] != 'All') {

	foreach ($_POST['PropValueText'] as $value) {
		if ($value != '') {
			$count++;
			if ($count <= 1)
				$SQL2 .= "		stockitemproperties.value like '" . $value . "'";
			else
				$SQL2 .= "		or stockitemproperties.value like '" . $value . "'";
		}
	}
}
if (isset($_POST['StockCat']) and $_POST['StockCat'] != 'All') {

	for ($i = 0; $i < count($_POST['PropValueMin']); $i++) {
		if ($_POST['PropValueMin'][$i] != '' && $_POST['PropValueMax'][$i] != '') {
			$count++;
			if ($count <= 1)
				$SQL2 .= "		CAST(stockitemproperties.value as integer) < '" . intval($_GET['PropValueMax'][$i]) . "'

and 		CAST(stockitemproperties.value as integer) > '" . intval($_POST['PropValueMin'][$i]) . "'
";
			else
				$SQL2 .= "		or stockitemproperties.value like '" . $value . "'";
		}
	}
}
$SQL2 .= ") order by locstock.quantity desc";


//echo $SQL2;
$ErrMsg = _('No stock items were returned by the SQL because');
$DbgMsg = _('The SQL that returned an error was');
if ($SQLA != '')

	$SQLA = $SQLA . ' LIMIT ' . $DefaultDisplayRecordsMax . ' OFFSET ' . ($DefaultDisplayRecordsMax * $Offset);


$SQL2 = $SQL2 . ' LIMIT ' . $DefaultDisplayRecordsMax . ' OFFSET ' . ($DefaultDisplayRecordsMax * $Offset);

if ($count > 0) {
	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL used to get the part selection was');

	$SearchResult = DB_query($SQL2, $db, $ErrMsg, $DbgMsg);
} else {
	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL used to get the part selection was');

	$SearchResult = DB_query($SQLA, $db, $ErrMsg, $DbgMsg);
}


if (DB_num_rows($SearchResult) == 0) {
	prnMsg(_('There are no products available meeting the criteria specified'), 'info');
}
if (DB_num_rows($SearchResult) < $_SESSION['DisplayRecordsMax']) {
	$Offset = 0;
}
//end of if search



/* display list if there is more than one record */
if (isset($SearchResult) and !isset($_POST['Select'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$ListCount = DB_num_rows($SearchResult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
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
				<input type="hidden" name=Keywords value="' . $_POST['Keywords'] . '" />
				<input type="hidden" name=StockCat value="' . $_POST['StockCat'] . '" />
				<input type="hidden" name=StockCode value="' . $_POST['StockCode'] . '" />
				<br />
				</div>';
		}

		//end of while loop
		echo '</table>
              </div>
              </form>
              <br />';
	}
}
/* end display list if there is more than one record */

if (isset($SearchResult)) {
	$j = 1;
	echo '<br />
		<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Order when ready.') . '</div>
		<br />
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" id="orderform">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table class="table1">
		<tr>
			<td>
				<input type="hidden" name="Previous" value="' . ($Offset - 1) . '" />
				<input tabindex="' . ($j + 8) . '" type="submit" name="Prev" value="' . _('Prev') . '" /></td>
				<td style="text-align:center" colspan="6">
				<input type="hidden" name="order_items" value="1" />
				<input tabindex="' . ($j + 9) . '" type="submit" value="' . _('Add to Requisition') . '" /></td>
			<td>
				<input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
				<input tabindex="' . ($j + 10) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>
			</tr>
			<tr>
				<th class="ascending">' . _('Code') . '</th>
				<th class="ascending">' . _('Description') . '</th>
				<th>' . _('Units') . '</th>
				<th class="ascending">' . _('On Hand') . '</th>
				<th class="ascending">' . _('On Demand') . '</th>
				<th class="ascending">' . _('On Order') . '</th>
				<th class="ascending">' . _('Available') . '</th>
				<th class="ascending">' . _('Quantity') . '</th>
			</tr>';
	$ImageSource = _('No Image');

	$k = 0; //row colour counter
	$i = 0;
	while ($myrow = DB_fetch_array($SearchResult)) {
		if ($myrow['decimalplaces'] == '') {
			$DecimalPlacesSQL = "SELECT decimalplaces
								FROM stockmaster
								WHERE stockid='" . $myrow['stockid'] . "'";
			$DecimalPlacesResult = DB_query($DecimalPlacesSQL, $db);
			$DecimalPlacesRow = DB_fetch_array($DecimalPlacesResult);
			$DecimalPlaces = $DecimalPlacesRow['decimalplaces'];
		} else {
			$DecimalPlaces = $myrow['decimalplaces'];
		}

		$QOHSQL = "SELECT sum(locstock.quantity) AS qoh
							   FROM locstock
							   WHERE locstock.stockid='" . $myrow['stockid'] . "' AND
							   loccode = '" . $_SESSION['Request']->Location . "'";
		$QOHResult =  DB_query($QOHSQL, $db);
		$QOHRow = DB_fetch_array($QOHResult);
		$QOH = $QOHRow['qoh'];

		// Find the quantity on outstanding sales orders
		$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
				 FROM salesorderdetails INNER JOIN salesorders
				 ON salesorders.orderno = salesorderdetails.orderno
				 WHERE salesorders.fromstkloc='" . $_SESSION['Request']->Location . "'
				 AND salesorderdetails.completed=0
				 AND salesorders.quotation=0
				 AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
		$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Request']->Location . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($sql, $db, $ErrMsg);

		$DemandRow = DB_fetch_row($DemandResult);
		if ($DemandRow[0] != null) {
			$DemandQty =  $DemandRow[0];
		} else {
			$DemandQty = 0;
		}

		// Find the quantity on purchase orders
		$sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
				 FROM purchorderdetails LEFT JOIN purchorders
					ON purchorderdetails.orderno=purchorders.orderno
				 WHERE purchorderdetails.completed=0
				 AND purchorders.status<>'Cancelled'
				 AND purchorders.status<>'Rejected'
				 AND purchorders.status<>'Completed'
				AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

		$ErrMsg = _('The order details for this product cannot be retrieved because');
		$PurchResult = DB_query($sql, $db, $ErrMsg);

		$PurchRow = DB_fetch_row($PurchResult);
		if ($PurchRow[0] != null) {
			$PurchQty =  $PurchRow[0];
		} else {
			$PurchQty = 0;
		}

		// Find the quantity on works orders
		$sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
			   FROM woitems
			   WHERE stockid='" . $myrow['stockid'] . "'";
		$ErrMsg = _('The order details for this product cannot be retrieved because');
		$WoResult = DB_query($sql, $db, $ErrMsg);

		$WoRow = DB_fetch_row($WoResult);
		if ($WoRow[0] != null) {
			$WoQty =  $WoRow[0];
		} else {
			$WoQty = 0;
		}

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		$OnOrder = $PurchQty + $WoQty;
		$Available = $QOH - $DemandQty + $OnOrder;
		echo '<td><a href = "SelectProduct.php?Select=' . $myrow['stockid'] . '" target = "_blank" >' . $myrow['stockid'] . '</a></td>
				
				<td>' . $myrow['description'] . '</td>
				<td>' . $myrow['stockunits'] . '</td>
				<td class="number">' . $myrow['qohand'] . '</td>
				<td class="number">' . locale_number_format($DemandQty, $DecimalPlaces) . '</td>
				<td class="number">' . locale_number_format($OnOrder, $DecimalPlaces) . '</td>
				<td class="number">' . locale_number_format($Available, $DecimalPlaces) . '</td>
				<td><input class="number" ' . ($i == 0 ? 'autofocus="autofocus"' : '') . ' tabindex="' . ($j + 7) . '" type="text" size="6" name="Quantity' . $i . '" value="0" />
				<input type="hidden" name="StockID' . $i . '" value="' . $myrow['stockid'] . '" />
				</td>
			</tr>';
		echo '<input type="hidden" name="DecimalPlaces' . $i . '" value="' . $myrow['decimalplaces'] . '" />';
		echo '<input type="hidden" name="ItemDescription' . $i . '" value="' . $myrow['description'] . '" />';
		echo '<input type="hidden" name="Units' . $i . '" value="' . $myrow['stockunits'] . '" />';
		$i++;
	}
	#end of while loop
	echo '<tr>
			<td><input type="hidden" name="Previous" value="' . ($Offset - 1) . '" />
				<input tabindex="' . ($j + 7) . '" type="submit" name="Prev" value="' . _('Prev') . '" /></td>
			<td style="text-align:center" colspan="6"><input type="hidden" name="order_items" value="1" />
				<input tabindex="' . ($j + 8) . '" type="submit" value="' . _('Add to Requisition') . '" /></td>
			<td><input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
				<input tabindex="' . ($j + 9) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>\
		<tr/>
		</table>
       </div>
       </form>';
} #end if SearchResults to show

//*********************************************************************************************************
include('includes/footer.inc');
