<?php

/* $Id: InternalStockRequest.php 4576 2011-05-27 10:59:20Z daintree $*/

include('includes/DefineIGPClass.php');

include('includes/session.inc');
$Title = _('Inwards Gate Pass');
$ViewTopic = 'Inventory';
$BookMark = 'CreateRequest';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$DefaultDisplayRecordsMax = 100;
if (isset($_POST['PropValue']))
	$_SESSION['PropValue'] = $_POST['PropValue'];
if (isset($_POST['PropValueText']))
	$_SESSION['PropValueText'] = $_POST['PropValueText'];
if (isset($_POST['PropValueMin']))
	$_SESSION['PropValueMin'] = $_POST['PropValueMin'];
if (isset($_POST['PropValueMax']))
	$_SESSION['PropValueMax'] = $_POST['PropValueMax'];

if (isset($_POST['igp'])) {
	$_SESSION['Request']->igp = $_POST['igp'];
	$_POST['igp'] = $_SESSION['Request']->igp;
}

if (isset($_POST['salescaseref'])) {
	$_SESSION['Request']->salescaseref = $_POST['salescaseref'];
	$_POST['salescaseref'] = $_SESSION['Request']->salescaseref;
}

if (isset($_POST['csv'])) {
	$_SESSION['Request']->csv = $_POST['csv'];
	$_POST['csv'] = $_SESSION['Request']->csv;
}

if (isset($_POST['crv'])) {
	$_SESSION['Request']->crv = $_POST['crv'];
	$_POST['crv'] = $_SESSION['Request']->crv;
}

if (isset($_POST['mpo'])) {
	$_SESSION['Request']->mpo = $_POST['mpo'];
	$_POST['mpo'] = $_SESSION['Request']->mpo;
}

if (isset($_POST['salespersonIgpType'])) {
	$_SESSION['Request']->salespersonIgpType = $_POST['salespersonIgpType'];
	$_POST['salespersonIgpType'] = $_SESSION['Request']->salespersonIgpType;
}
if (isset($_POST['receivedfrom']))

	$_SESSION['Request']->receivedfrom = $_POST['receivedfrom'];

if (isset($_POST['brand']))
	$_SESSION['brand'] = $_POST['brand'];
if (isset($_POST['StockCode']))
	$_SESSION['StockCode'] = $_POST['StockCode'];
if (isset($_POST['wo']))
	$_SESSION['wo'] = $_POST['wo'];



if (isset($_POST['StockCat']))
	$_SESSION['StockCat'] = $_POST['StockCat'];
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
		$SQL = "SELECT * from manufacturers";
	} else {
		$SQL = "SELECT distinct manufacturers_id,
				manufacturers_name
		FROM manufacturers, stockmaster, stockcategory
		where stockmaster.brand = manufacturers.manufacturers_id
		and stockmaster.categoryid = stockcategory.categoryid
		and stockcategory.categoryid = " . "'" . $_POST['StockCat'] . "'" . "
		ORDER BY manufacturers_name";
	}
	$result2 = DB_query($SQL, $db);
} else {
	$SQL = "select * from manufacturers";
	$result2 = DB_query($SQL, $db);
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
/*
if (isset($_SESSION['UserStockLocation']))

$_SESSION['Request']->Location=$_SESSION['UserStockLocation'];
		if (isset($_POST['source']))

		$_SESSION['Request']->source=$_POST['source'];
if (isset($_POST['reference']))

		$_SESSION['Request']->reference=$_POST['reference'];
if (isset($_POST['DispatchDate']))
		
		$_SESSION['Request']->DispatchDate=$_POST['DispatchDate'];
if (isset($_POST['Narrative']))

		$_SESSION['Request']->Narrative=$_POST['Narrative'];
if (isset($_POST['UsersRealName']))

		$_SESSION['Request']->storemanager=$_SESSION['UsersRealName'];
if (isset($_POST['receivedfrom']))

		$_SESSION['Request']->receivedfrom=$_POST['receivedfrom']; 
if (isset($_POST['substore']))

		$_SESSION['Request']->substore = $_POST['substore'];
	
		$_SESSION['Request']->igp = $_POST['igp'];
	*/
if (isset($_POST['Update'])) {
	$InputError = 0;

	if ($InputError == 0) {
		$_SESSION['Request']->Location = $_SESSION['UserStockLocation'];
		$_SESSION['Request']->source = $_POST['source'];
		$_SESSION['Request']->reference = $_POST['reference'];

		$_SESSION['Request']->DispatchDate = $_POST['DispatchDate'];
		$_SESSION['Request']->Narrative = $_POST['Narrative'];
		$_SESSION['Request']->storemanager = $_SESSION['UsersRealName'];
		$_SESSION['Request']->substore = $_POST['substore'];
		$_SESSION['Request']->receivedfrom = $_POST['receivedfrom'];
		//$_SESSION['Request']->igp = $_POST['igp'];
	}
}

if (isset($_GET['Edit'])) {
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
			$_SESSION['Request']->AddLine($StockID, $ItemDescription, $NewItem_array[$StockID], $NewComment_array[$StockID], $_POST['Units' . $StockID], null, null, $DecimalPlaces);
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

	if ($_SESSION['Request']->Location == '') {
		prnMsg(_('You must select a Location to request the items from'), 'error');
		$InputError = 1;
	}
	if ($InputError == 0) {

		$RequestNo = GetNextTransNo(38, $db);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

		$HeaderSQL = "INSERT INTO igp (dispatchid,
											loccode,
											
											despatchdate,
											
											
											receivedfrom,
											storemanager,
											narrative)
										VALUES(
											'" . $RequestNo . "',
											'" . $_SESSION['Request']->Location . "',
											'" . FormatDateForSQL($_SESSION['Request']->DispatchDate) . "',
											
											'" . $_SESSION['Request']->receivedfrom . "',
											'" . $_SESSION['Request']->storemanager . "',
											'" . $_SESSION['Request']->Narrative . "')";
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);


		if ($_SESSION['Request']->salescaseref) {
			foreach ($_SESSION['Request']->LineItems as $LineItems) {

				$HeaderSalescaserefSQL = "UPDATE ogpsalescaseref
				SET quantity = quantity - " . $LineItems->Quantity . "
				WHERE stockid='" . $LineItems->StockID . "'
				AND salesman ='" . $_SESSION['Request']->receivedfrom . "'
				AND salescaseref='" . $_SESSION['Request']->salescaseref . "'
				AND quantity != 0";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSalescaserefSQL, $db, $ErrMsg, $DbgMsg, true);

				$HeaderSQL = "INSERT INTO ogpsalescaseref (dispatchid,
											salescaseref,
											requestedby)
										VALUES(
											'" . $RequestNo . "',
											'" . $_SESSION['Request']->salescaseref . "',
											'" . $_SESSION['UsersRealName'] . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
			}
		}

		if ($_SESSION['Request']->csv) {
			foreach ($_SESSION['Request']->LineItems as $LineItems) {

				$HeaderSalescaserefSQL = "UPDATE ogpcsvref
				SET quantity = quantity - " . $LineItems->Quantity . "
				WHERE stockid='" . $LineItems->StockID . "'
				AND salesman ='" . $_SESSION['Request']->receivedfrom . "'
				AND csv ='" . $_SESSION['Request']->csv . "'
				AND quantity != 0 ";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSalescaserefSQL, $db, $ErrMsg, $DbgMsg, true);

				$HeaderSQL = "INSERT INTO ogpcsvref (dispatchid,
											csv,
											requestedby)
										VALUES(
											'" . $RequestNo . "',
											'" . $_SESSION['Request']->csv . "',
											'" . $_SESSION['UsersRealName'] . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
			}
		}

		if ($_SESSION['Request']->crv) {
			foreach ($_SESSION['Request']->LineItems as $LineItems) {

				$HeaderSalescaserefSQL = "UPDATE ogpcrvref
				SET quantity = quantity - " . $LineItems->Quantity . "
				WHERE stockid='" . $LineItems->StockID . "'
				AND salesman ='" . $_SESSION['Request']->receivedfrom . "'
				AND crv ='" . $_SESSION['Request']->crv . "'
				AND quantity != 0 ";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSalescaserefSQL, $db, $ErrMsg, $DbgMsg, true);

				$HeaderSQL = "INSERT INTO ogpcrvref (dispatchid,
											crv,
											requestedby)
										VALUES(
											'" . $RequestNo . "',
											'" . $_SESSION['Request']->crv . "',
											'" . $_SESSION['UsersRealName'] . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
			}
		}

		if ($_SESSION['Request']->mpo) {
			foreach ($_SESSION['Request']->LineItems as $LineItems) {

				$HeaderSalescaserefSQL = "UPDATE ogpmporef
				SET quantity = quantity - " . $LineItems->Quantity . "
				WHERE stockid='" . $LineItems->StockID . "'
				AND salesman ='" . $_SESSION['Request']->receivedfrom . "'
				AND mpo ='" . $_SESSION['Request']->mpo . "'
				AND quantity != 0";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSalescaserefSQL, $db, $ErrMsg, $DbgMsg, true);

				$HeaderSQL = "INSERT INTO ogpmporef (dispatchid,
											mpo,
											requestedby)
										VALUES(
											'" . $RequestNo . "',
											'" . $_SESSION['Request']->mpo . "',
											'" . $_SESSION['UsersRealName'] . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);
			}
		}

		foreach ($_SESSION['Request']->LineItems as $LineItems) {
			$LineSQL = "INSERT INTO igpitems (dispatchitemsid,
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
			$SQL = "SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $LineItems->StockID . "'
						AND loccode= '" . $_SESSION['UserStockLocation'] . "'";

			$ResultQ = DB_query($SQL, $db);
			if (DB_num_rows($ResultQ) == 1) {
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
						510,
						'" . $RequestNo . "',
						'" . $_SESSION['Request']->Location . "',
							'" . FormatDateForSQL($_SESSION['Request']->DispatchDate) . "',
						'" . _('From') . ' ' . DB_escape_string($_SESSION['Request']->receivedfrom) . "'
						,'" . round($LineItems->Quantity, 0) . "'
						,'" . $PeriodNo . "'
						
						,'" . round($QtyOnHandPrior + $LineItems->Quantity, 0) . "'
						)";

			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the incoming stock cannot be added because');
			$DbgMsg =  _('The following SQL to insert the stock movement record was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			if ($_SESSION['Request']->igp == "W") {
				$SQL = "UPDATE woitems
					SET qtyrecd = qtyrecd + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND wo='" . $_SESSION['wo'] . "'";
				$Result = DB_query($SQL, $db);




				$SQL = "UPDATE locstock
					SET quantity = quantity + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND loccode='" . $_SESSION['Request']->Location . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE substorestock
					SET quantity = quantity + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND substoreid='" . $_SESSION['Request']->substore . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				$SQL = "SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $TrfLine->StockID . "'
						AND loccode= '" . $_SESSION['Transfer']->StockLocationTo . "'";

				$Result = DB_query($SQL, $db,  _('Could not retrieve the quantity on hand at the location being transferred to'));
				if (DB_num_rows($Result) == 1) {
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}
			}





			if ($_SESSION['Request']->igp == "E") {
				$SQL = "UPDATE workorderissuance
					SET issued = issued - '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND engineer='" . $_SESSION['Request']->receivedfrom . "'";
				$Result = DB_query($SQL, $db);

				$SQL = "UPDATE workorderissuance
					SET returned = returned + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND engineer='" . $_SESSION['Request']->receivedfrom . "'";
				$Result = DB_query($SQL, $db);


				$SQL = "UPDATE locstock
					SET quantity = quantity + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND loccode='" . $_SESSION['Request']->Location . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE substorestock
					SET quantity = quantity + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND substoreid='" . $_SESSION['Request']->substore . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				$SQL = "SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $TrfLine->StockID . "'
						AND loccode= '" . $_SESSION['Transfer']->StockLocationTo . "'";

				$Result = DB_query($SQL, $db,  _('Could not retrieve the quantity on hand at the location being transferred to'));
				if (DB_num_rows($Result) == 1) {
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}
			}


			if ($_SESSION['Request']->igp == "A") {
				$SQL = "UPDATE stockissuance
					SET issued = issued - '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND salesperson='" . $_SESSION['Request']->receivedfrom . "'";
				$Result = DB_query($SQL, $db);

				$SQL = "UPDATE stockissuance
					SET returned = returned + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND salesperson='" . $_SESSION['Request']->receivedfrom . "'";
				$Result = DB_query($SQL, $db);


				$SQL = "UPDATE locstock
					SET quantity = quantity + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND loccode='" . $_SESSION['Request']->Location . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE substorestock
					SET quantity = quantity + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND substoreid='" . $_SESSION['Request']->substore . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				$SQL = "SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $TrfLine->StockID . "'
						AND loccode= '" . $_SESSION['Transfer']->StockLocationTo . "'";

				$Result = DB_query($SQL, $db,  _('Could not retrieve the quantity on hand at the location being transferred to'));
				if (DB_num_rows($Result) == 1) {
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}
			}

			if ($_SESSION['Request']->igp == "B" or $_SESSION['Request']->igp == "C") {

				$SQL = "UPDATE locstock
					SET quantity = quantity + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND loccode='" . $_SESSION['Request']->Location . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE substorestock
					SET quantity = quantity + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND substoreid='" . $_SESSION['Request']->substore . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
			}
		}
	}
	DB_Txn_Commit($db);
	echo '<p><a href="' . $RootPath . '/PDFIGP.php?RequestNo=' . $RequestNo . '">' .  _('Print the IGP') . '</a></p>';

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

?>
<!DOCTYPE html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</head>
<script>
	$(document).ready(function() {
		$('.js-example-basic-single').select2();
	});
</script>

<body>

	<?php
	echo '<form name = "formA" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';
	echo '<tr>
		<th colspan="2"><h4>' . _('Inwards Gate Pass') . '</h4></th>
	</tr>';
	echo '	<tr>
		<td>' . _('Select IGP Type ') . ':</td>';
	echo '<td><select name="igp" onchange="ReloadForm(formA.UpdateForm)">';

	echo '<option value="">' . _('Select IGP Type') . '</option>';
	if (isset($_SESSION['Request']->igp) and $_SESSION['Request']->igp == "A")

		echo '<option value="A" selected = "selected">' . _('Returned From Sales Person') . '</option>';
	else echo '<option value="A">' . _('Returned From Sales Person') . '</option>';

	if (isset($_SESSION['Request']->igp) and $_SESSION['Request']->igp == "E")

		echo '<option value="E" selected = "selected">' . _('Returned From Engineer') . '</option>';
	else echo '<option value="E">' . _('Returned From Engineer') . '</option>';

	if (isset($_SESSION['Request']->igp) and $_SESSION['Request']->igp == "W")

		echo '<option value="W" selected = "selected">' . _('Receive Work Order Item') . '</option>';
	else echo '<option value="W">' . _('Receive Work Order Item') . '</option>';


	if (isset($_SESSION['Request']->igp) and $_SESSION['Request']->igp == "B")

		echo '<option value="B" selected = "selected">' . _('Received from store location') . '</option>';
	else echo '<option value="B">' . _('Received from store location') . '</option>';

	if (isset($_SESSION['Request']->igp) and $_SESSION['Request']->igp == "C")
		echo '<option value="C" selected = "selected">' . _('External source') . '</option>';


	else echo '<option value="C">' . _('External source') . '</option>';




	echo '</select></td>
	<tr>';
	if (isset($_SESSION['Request']->igp) and $_SESSION['Request']->igp == "E") {


		echo '
		<td>' . _('Returned from engineer ') . ':</td>';
		$sql = "select realname from www_users where defaultlocation = '" . $_SESSION['UserStockLocation'] . "' and fullaccess = 24";
		$result = DB_query($sql, $db);
		echo '<td><select name="receivedfrom">
<option value="">' . _('Select an engineer') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->receivedfrom) and $_SESSION['Request']->receivedfrom == $myrow['realname']) {
				echo '<option selected="True" value="' . $myrow['realname'] . '">' . htmlspecialchars($myrow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['realname'] . '">' . htmlspecialchars($myrow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}


		echo '</select></td></tr>';
	}
	if (isset($_SESSION['Request']->igp) and $_SESSION['Request']->igp == "W") {


		echo '
		<td>' . _('Work Order Number ') . ':</td><td> <input type = "text" name = "wo"></td>';
	}


	if (isset($_SESSION['Request']->igp) and $_SESSION['Request']->igp == "A") {


		echo '
		<td>' . _('Returned from employee ') . ':</td>';
		$sql = "select realname from www_users where defaultlocation = '" . $_SESSION['UserStockLocation'] . "'";
		$result = DB_query($sql, $db);
		echo '<td><select name="receivedfrom" onchange="ReloadForm(formA.UpdateForm)">
		<option value="">' . _('Select a Sales Person') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->receivedfrom) and $_SESSION['Request']->receivedfrom == $myrow['realname']) {
				echo '<option selected="True" value="' . $myrow['realname'] . '">' . htmlspecialchars($myrow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['realname'] . '">' . htmlspecialchars($myrow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}


		echo '</select></td></tr>';
		echo '	
				<td>' . _('Salesperson IGP Type ') . ':</td>';
		echo '<td><select name="salespersonIGPType" onchange="ReloadForm(formA.UpdateForm)">';

		echo '<option value="">' . _('Salesperson IGP Type') . '</option>';
		if (isset($_POST['salespersonIGPType']) and $_POST['salespersonIGPType'] == "A")
			echo '<option value="A" selected = "selected">' . _('From Salescase') . '</option>';


		else echo '<option value="A">' . _('From Salescase') . '</option>';

		if (isset($_POST['salespersonIGPType']) and $_POST['salespersonIGPType'] == "B")

			echo '<option value="B" selected = "selected">' . _('From CSV') . '</option>';
		else echo '<option value="B">' . _('From CSV') . '</option>';

		if (isset($_POST['salespersonIGPType']) and $_POST['salespersonIGPType'] == "C")

			echo '<option value="C" selected = "selected">' . _('From CRV') . '</option>';
		else echo '<option value="C">' . _('From CRV') . '</option>';

		if (isset($_POST['salespersonIGPType']) and $_POST['salespersonIGPType'] == "D")

			echo '<option value="D" selected = "selected">' . _('From MPO') . '</option>';
		else echo '<option value="D">' . _('From MPO') . '</option>';

		if (isset($_POST['salespersonIGPType']) and $_POST['salespersonIGPType'] == "E")

			echo '<option value="E" selected = "selected">' . _('From Cart') . '</option>';
		else echo '<option value="E">' . _('From Cart') . '</option>';

		echo '</select></td>';
	}
	?>
	<script>
		$(document).ready(function() {
			$('.js-example-basic-single').select2();
		});
	</script>
	<?php
	echo '<tr>';
	if (isset($_POST['salespersonIGPType']) and $_POST['salespersonIGPType'] == "A") {
		echo '
		<td>' . _('From salescase ') . ':</td>';
		$sql = "select salescaseref,salescaseindex from salescase WHERE salesman = '" . $_SESSION['Request']->receivedfrom . "' ";
		$result = DB_query($sql, $db);
		echo '<td><select class="js-example-basic-single" name="salescaseref" onchange="ReloadForm(formA.UpdateForm)">
	<option value="">' . _('Select a Salescase') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->salescaseref) and $_SESSION['Request']->salescaseref == $myrow['salescaseref']) {
				echo '<option selected="True" value="' . $myrow['salescaseref'] . '">' . htmlspecialchars($myrow['salescaseref'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['salescaseref'] . '">' . htmlspecialchars($myrow['salescaseref'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}


		echo '</select></td>';
	}

	echo ' </tr>';
	if (isset($_POST['salespersonIGPType']) and $_POST['salespersonIGPType'] == "B") {
		echo '
	<td>' . _('From CSV ') . ':</td>';
		$sql = "select orderno from shopsale WHERE salesman = '" . $_SESSION['Request']->receivedfrom . "' AND 
		payment = 'csv'";
		$result = DB_query($sql, $db);
		echo '<td><select class="js-example-basic-single" name="csv" onchange="ReloadForm(formA.UpdateForm)">
<option value="">' . _('Select a CSV') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->csv) and $_SESSION['Request']->csv == $myrow['orderno']) {
				echo '<option selected="True" value="' . $myrow['orderno'] . '">' . htmlspecialchars($myrow['orderno'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['orderno'] . '">' . htmlspecialchars($myrow['orderno'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}
		echo '</select></td>';
	}
	echo ' </tr>';

	echo ' </tr>';
	if (isset($_POST['salespersonIGPType']) and $_POST['salespersonIGPType'] == "C") {
		echo '
		<td>' . _('From CRV ') . ':</td>';
		$sql = "select orderno from shopsale WHERE salesman = '" . $_SESSION['Request']->receivedfrom . "' AND 
			payment = 'crv'";
		$result = DB_query($sql, $db);
		echo '<td><select class="js-example-basic-single" name="crv">
	<option value="">' . _('Select a CRV') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->crv) and $_SESSION['Request']->crv == $myrow['orderno']) {
				echo '<option selected="True" value="' . $myrow['orderno'] . '">' . htmlspecialchars($myrow['orderno'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['orderno'] . '">' . htmlspecialchars($myrow['orderno'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}
		echo '</select></td>';
	}
	echo ' </tr>';


	echo ' </tr>';
	if (isset($_POST['salespersonIGPType']) and $_POST['salespersonIGPType'] == "D") {
		echo '
		<td>' . _('From MPO ') . ':</td>';
		$sql = "SELECT * FROM `bazar_parchi` WHERE `on_behalf_of` = '" . $_SESSION['Request']->receivedfrom . "' ";
		$result = DB_query($sql, $db);
		echo '<td><select class="js-example-basic-single" name="mpo">
<option value="">' . _('Select a Parchi No') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->mpo) and $_SESSION['Request']->mpo == $myrow['parchino']) {
				echo '<option selected="True" value="' . $myrow['parchino'] . '">' . htmlspecialchars($myrow['parchino'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['parchino'] . '">' . htmlspecialchars($myrow['parchino'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}
		echo '</select></td>';
	}
	echo ' </tr>';

	echo ' </tr>';
	if (isset($_POST['salespersonIGPType']) and $_POST['salespersonIGPType'] == "E") {
	}
	echo ' </tr>';

	if (isset($_SESSION['Request']->igp) and $_SESSION['Request']->igp == "B") {
		// $_SESSION['Request']->igp = $_POST['igp'];

		echo	'<tr>
		<td>' . _('From Stock Location') . ':</td>';
		$sql = "SELECT loccode,
			locationname
		FROM locations
			
		";

		$result = DB_query($sql, $db);
		echo '<td><select name="receivedfrom">
		<option value="">' . _('Select a location') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->receivedfrom) and $_SESSION['Request']->receivedfrom == $myrow['locationname']) {
				echo '<option selected="True" value="' . $myrow['locationname'] . '">' . $myrow['loccode'] . ' - ' . htmlspecialchars($myrow['locationname'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['locationname'] . '">' . $myrow['loccode'] . ' - ' . htmlspecialchars($myrow['locationname'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}
		echo '</select></td></tr>
	';
	}
	if (isset($_SESSION['Request']->igp) and $_SESSION['Request']->igp == "C") {


		echo '<tr>
		<td>' . _('External Source') . ':</td>
		<td><textarea required name="receivedfrom" cols="30" rows="5">' . $_SESSION['Request']->receivedfrom . '</textarea></td>
	</tr>';
	}
	echo '	
	<tr>
		<td>' . _('Substore') . ':</td>';
	$sql = "SELECT substoreid,
			description
		FROM substores
		WHERE locid = '" . $_SESSION['UserStockLocation'] . "'
			
		";

	$result = DB_query($sql, $db);
	echo '<td><select name="substore">';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_SESSION['Request']->substore) and $_SESSION['Request']->substore == $myrow['substoreid']) {
			echo '<option selected="True" value="' . $myrow['substoreid'] . '">' . $myrow['substoreid'] . ' - ' . htmlspecialchars($myrow['description'], ENT_QUOTES, 'UTF-8') . '</option>';
		} else {
			echo '<option value="' . $myrow['substoreid'] . '">' . $myrow['substoreid'] . ' - ' . htmlspecialchars($myrow['description'], ENT_QUOTES, 'UTF-8') . '</option>';
		}
	}
	echo '</select></td>
	</tr>';

	echo '	<tr>
		<td>' . _('Date of return') . ':</td>';
	echo '<td><input required = "required" type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="DispatchDate" maxlength="10" size="11" value="' . $_SESSION['Request']->DispatchDate . '" /></td>
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
    </div>';
	echo '<input type="submit" name="UpdateForm" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />

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
	if ($_SESSION['Request']->igp == 'E') {
		if (isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Edit']) or isset($_POST['Previous'])) {
			$_POST['Update'] = 'Update';
		}
		if (isset($_POST['Update']) or isset($_POST['Go']) or isset($_POST['Edit']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
			if (!isset($_POST['Go']) and !isset($_POST['Next']) and !isset($_POST['Previous'])) {
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
						workorderissuance.issued AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN workorderissuance
					ON stockmaster.stockid=workorderissuance.stockid
					where workorderissuance.engineer = '" . $_SESSION['Request']->receivedfrom . "'
					and workorderissuance.issued>0
					AND stockmaster.stockid NOT LIKE '%\t%'
					order by workorderissuance.issued desc
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
		if (!isset($Offset) or $Offset < 0) {
			$Offset = 0;
		}
		$SQL = $SQL5 . ' LIMIT ' . $DefaultDisplayRecordsMax . ' OFFSET ' . ($DefaultDisplayRecordsMax * $Offset);

		//$ErrMsg = _('There is a problem selecting the part records to display because');
		//$DbgMsg = _('The SQL used to get the part selection was');
		$UpdateResult = DB_query($SQL5, $db);

		if (DB_num_rows($UpdateResult) == 0) {
			prnMsg(_('There are no products available meeting the criteria specified'), 'info');
		}
		if (DB_num_rows($UpdateResult) < $_SESSION['DisplayRecordsMax']) {
			$Offset = 0;
		}

		//end of if Update
		/* display list if there is more than one record */
		if (isset($Updateresult) and !isset($_POST['Select'])) {
			echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST">';
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
				<input type="hidden" name=Keywords value="' . $_POST['Keywords'] . '" />
				<input type="hidden" name=StockCat value="' . $_POST['StockCat'] . '" />
				<input type="hidden" name=StockCode value="' . $_POST['StockCode'] . '" />
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
				while (($myrow = DB_fetch_array($Updateresult)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
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
					if ($myrow['discontinued'] == 1) {
						$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
					} else {
						$ItemStatus = '';
					}

					echo '<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
					<td>' . $myrow['description'] . '</td>
					<td>' . $myrow['mnfCode'] . '</td>
					<td class="number">' . $qoh . '</td>
					<td>' . $myrow['units'] . '</td>
					<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'] . '">' . _('View') . '</a></td>
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
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST" id="orderform">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table class="table1">
		<tr>
			<td>
				<input type="hidden" name="Previous" value="' . ($Offset - 1) . '" />
				<input tabindex="' . ($j + 8) . '" type="submit" name="Prev" value="' . _('Prev') . '" /></td>
				<td style="text-align:center" colspan="6">
				<input type="hidden" name="order_items" value="1" />
				<input tabindex="' . ($j + 9) . '" type="submit" value="' . _('Add to IGP') . '" /></td>
			<td>
				<input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
				<input tabindex="' . ($j + 10) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>
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

			$k = 0; //row colour counter
			$i = 0;
			$QOH = array();
			while ($myrow = DB_fetch_array($UpdateResult)) {
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

				$QOHSQL = "SELECT issued AS qoh
							   FROM workorderissuance
							   WHERE workorderissuance.stockid='" . $myrow['stockid'] . "' AND
							   workorderissuance.engineer = '" . $_SESSION['Request']->receivedfrom . "'";
				$QOHResult =  DB_query($QOHSQL, $db);
				$QOHRow = DB_fetch_array($QOHResult);
				$q = 'Quantity' . $i;
				$p = 'ItemDescription' . $i;
				$QOH[$i] = $QOHRow['qoh'];
				for ($foo = 0; $foo <= count($_SESSION['Request']->LineItems); $foo++) {

					//echo $_SESSION['Request']->LineItems[$Index]->StockID;
					if ($_SESSION['Request']->LineItems[$foo]->ItemDescription == $_POST[$p]) {
						$QOH[$i] = $QOHRow['qoh'] - $_SESSION['Request']->LineItems[$foo]->Quantity - $_POST[$q];
					}
				}

				$QOH[$i] = $QOH[$i] - $_POST[$q];




				// Find the quantity on outstanding sales orders


				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				$OnOrder = $PurchQty + $WoQty;
				$Available = $QOH[$i] - $DemandQty + $OnOrder;
				echo '<td><a href = "SelectProduct.php?Select=' . $myrow['stockid'] . '" target = "_blank" >' . $myrow['stockid'] . '</a></td>
				
				<td>' . $myrow['description'] . '</td>
				<td>' . $myrow['mnfCode'] . '</td>
				
				<td>' . $myrow['stockunits'] . '</td>
				<td class="number">' . locale_number_format($QOH[$i], $DecimalPlaces) . '</td>
			<td><input class="number" ' . ($i == 0 ? 'autofocus="autofocus"' : '') . ' tabindex="' . ($j + 7) . '" type="text" size="6" name="Quantity' . $i . '" value="0" />
				<td><input  tabindex="' . ($j + 7) . '" type="text" size="26" name="Comments' . $i . '" value=" " />
				
				
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
				<input tabindex="' . ($j + 8) . '" type="submit" value="' . _('Add to IGP') . '" /></td>
			<td><input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
				<input tabindex="' . ($j + 9) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>
		<tr/>
		</table>
       </div>
       </form>';
		}
	} else if ($_SESSION['Request']->igp == 'W') {
		if (isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Edit']) or isset($_POST['Previous'])) {
			$_POST['Update'] = 'Update';
		}
		if (isset($_POST['Update']) or isset($_POST['Go']) or isset($_POST['Edit']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
			if (!isset($_POST['Go']) and !isset($_POST['Next']) and !isset($_POST['Previous'])) {
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
						woitems.qtyreqd AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN woitems
					ON stockmaster.stockid=woitems.stockid
					where woitems.wo = '" . $_SESSION['wo'] . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
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
		if (!isset($Offset) or $Offset < 0) {
			$Offset = 0;
		}
		$SQL = $SQL5 . ' LIMIT ' . $DefaultDisplayRecordsMax . ' OFFSET ' . ($DefaultDisplayRecordsMax * $Offset);

		//$ErrMsg = _('There is a problem selecting the part records to display because');
		//$DbgMsg = _('The SQL used to get the part selection was');
		$UpdateResult = DB_query($SQL5, $db);

		if (DB_num_rows($UpdateResult) == 0) {
			prnMsg(_('There are no products available meeting the criteria specified'), 'info');
		}
		if (DB_num_rows($UpdateResult) < $_SESSION['DisplayRecordsMax']) {
			$Offset = 0;
		}

		//end of if Update
		/* display list if there is more than one record */
		if (isset($Updateresult) and !isset($_POST['Select'])) {
			echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST">';
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
				<input type="hidden" name=Keywords value="' . $_POST['Keywords'] . '" />
				<input type="hidden" name=StockCat value="' . $_POST['StockCat'] . '" />
				<input type="hidden" name=StockCode value="' . $_POST['StockCode'] . '" />
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
				while (($myrow = DB_fetch_array($Updateresult)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
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
					if ($myrow['discontinued'] == 1) {
						$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
					} else {
						$ItemStatus = '';
					}

					echo '<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
					<td>' . $myrow['description'] . '</td>
					<td>' . $myrow['mnfCode'] . '</td>
					<td class="number">' . $qoh . '</td>
					<td>' . $myrow['units'] . '</td>
					<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'] . '">' . _('View') . '</a></td>
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
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST" id="orderform">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table class="table1">
		<tr>
			<td>
				<input type="hidden" name="Previous" value="' . ($Offset - 1) . '" />
				<input tabindex="' . ($j + 8) . '" type="submit" name="Prev" value="' . _('Prev') . '" /></td>
				<td style="text-align:center" colspan="6">
				<input type="hidden" name="order_items" value="1" />
				<input tabindex="' . ($j + 9) . '" type="submit" value="' . _('Add to IGP') . '" /></td>
			<td>
				<input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
				<input tabindex="' . ($j + 10) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>
			</tr>
			<tr>
				<th class="ascending">' . _('Code') . '</th>
				<th class="ascending">' . _('Description') . '</th>
				<th class="ascending">' . _('Model No.') . '</th>
				
				<th>' . _('Units') . '</th>
				<th class="ascending">' . _('Qty Reqd') . '</th>
				
				<th class="ascending">' . _('Quantity') . '</th>
				<th class="ascending">' . _(' ') . '</th>
			</tr>';
			$ImageSource = _('No Image');

			$k = 0; //row colour counter
			$i = 0;
			$QOH = array();
			while ($myrow = DB_fetch_array($UpdateResult)) {
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

				$QOHSQL = "SELECT qtyreqd AS qoh
							   FROM woitems
							   WHERE woitems.stockid='" . $myrow['stockid'] . "' ";
				$QOHResult =  DB_query($QOHSQL, $db);
				$QOHRow = DB_fetch_array($QOHResult);
				$q = 'Quantity' . $i;
				$p = 'ItemDescription' . $i;
				$QOH[$i] = $QOHRow['qoh'];
				for ($foo = 0; $foo <= count($_SESSION['Request']->LineItems); $foo++) {

					//echo $_SESSION['Request']->LineItems[$Index]->StockID;
					if ($_SESSION['Request']->LineItems[$foo]->ItemDescription == $_POST[$p]) {
						$QOH[$i] = $QOHRow['qoh'] - $_SESSION['Request']->LineItems[$foo]->Quantity - $_POST[$q];
					}
				}

				$QOH[$i] = $QOH[$i] - $_POST[$q];




				// Find the quantity on outstanding sales orders


				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				$OnOrder = $PurchQty + $WoQty;
				$Available = $QOH[$i] - $DemandQty + $OnOrder;
				echo '<td><a href = "SelectProduct.php?Select=' . $myrow['stockid'] . '" target = "_blank" >' . $myrow['stockid'] . '</a></td>
				
				<td>' . $myrow['description'] . '</td>
				<td>' . $myrow['mnfCode'] . '</td>
				
				<td>' . $myrow['stockunits'] . '</td>
				<td class="number">' . locale_number_format($QOH[$i], $DecimalPlaces) . '</td>
			<td><input class="number" ' . ($i == 0 ? 'autofocus="autofocus"' : '') . ' tabindex="' . ($j + 7) . '" type="text" size="6" name="Quantity' . $i . '" value="0" />
				<td><input  tabindex="' . ($j + 7) . '" type="text" size="26" name="Comments' . $i . '" value=" " />
				
				
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
				<input tabindex="' . ($j + 8) . '" type="submit" value="' . _('Add to IGP') . '" /></td>
			<td><input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
				<input tabindex="' . ($j + 9) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>
		<tr/>
		</table>
       </div>
       </form>';
		}
	} else 
if ($_SESSION['Request']->igp == 'A') {
		if (isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Edit']) or isset($_POST['Previous'])) {
			$_POST['Update'] = 'Update';
		}
		if (isset($_POST['Update']) or isset($_POST['Go']) or isset($_POST['Edit']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
			if (!isset($_POST['Go']) and !isset($_POST['Next']) and !isset($_POST['Previous'])) {
				// if Update then set to first page
				$_POST['PageOffset'] = 1;
			}

			// From Salescase 
			if ($_POST['salespersonIGPType'] == "A") {
				$SQL5 = "SELECT DISTINCT stockmaster.stockid,
				stockmaster.description,
				stockmaster.mnfCode,
				stockmaster.longdescription,
				stockmaster.mnfCode,
				stockmaster.mnfpno,
				stockmaster.mbflag,
				stockmaster.discontinued,
				stockissuance.issued AS qoh,
				stockmaster.units,
				stockmaster.decimalplaces,
				ogpsalescaseref.salescaseref
					FROM stockmaster INNER JOIN stockissuance
					ON stockmaster.stockid=stockissuance.stockid
					INNER JOIN ogpsalescaseref
					ON stockmaster.stockid=ogpsalescaseref.stockid
					where stockissuance.salesperson = '" . $_SESSION['Request']->receivedfrom . "'
					AND ogpsalescaseref.salescaseref = '" . $_SESSION['Request']->salescaseref . "'
					AND ogpsalescaseref.quantity != ''
					AND ogpsalescaseref.quantity != '0'
					and stockissuance.issued>0
					AND stockmaster.stockid NOT LIKE '%\t%'
					order by stockissuance.issued desc
					";

				$UpdateResult = DB_query($SQL5, $db);
			}

			// From csv 
			if ($_POST['salespersonIGPType'] == "B") {
				$SQL5 = "SELECT DISTINCT stockmaster.stockid,
				stockmaster.description,
				stockmaster.mnfCode,
				stockmaster.longdescription,
				stockmaster.mnfCode,
				stockmaster.mnfpno,
				stockmaster.mbflag,
				stockmaster.discontinued,
				stockissuance.issued  AS qoh,
				stockmaster.units,
				stockmaster.decimalplaces,
				ogpcsvref.csv
					FROM stockmaster INNER JOIN stockissuance
					ON stockmaster.stockid=stockissuance.stockid
					INNER JOIN ogpcsvref
					ON stockmaster.stockid=ogpcsvref.stockid
					where stockissuance.salesperson = '" . $_SESSION['Request']->receivedfrom . "'
					AND ogpcsvref.csv = '" . $_SESSION['Request']->csv . "'
					AND ogpcsvref.quantity != ''
					AND ogpcsvref.quantity != '0'
					and stockissuance.issued>0
					AND stockmaster.stockid NOT LIKE '%\t%'
					order by stockissuance.issued desc
					";

				$UpdateResult = DB_query($SQL5, $db);
			}
			// From crv 
			if ($_POST['salespersonIGPType'] == "C") {
				$SQL5 = "SELECT DISTINCT stockmaster.stockid,
				stockmaster.description,
				stockmaster.mnfCode,
				stockmaster.longdescription,
				stockmaster.mnfCode,
				stockmaster.mnfpno,
				stockmaster.mbflag,
				stockmaster.discontinued,
				stockissuance.issued  AS qoh,
				stockmaster.units,
				stockmaster.decimalplaces,
				ogpcrvref.crv
					FROM stockmaster INNER JOIN stockissuance
					ON stockmaster.stockid=stockissuance.stockid
					INNER JOIN ogpcrvref
					ON stockmaster.stockid=ogpcrvref.stockid
					where stockissuance.salesperson = '" . $_SESSION['Request']->receivedfrom . "'
					AND ogpcrvref.crv = '" . $_SESSION['Request']->crv . "'
					AND ogpcrvref.quantity != ''
					AND ogpcrvref.quantity != '0'
					and stockissuance.issued>0
					AND stockmaster.stockid NOT LIKE '%\t%'
					order by stockissuance.issued desc
					";

				$UpdateResult = DB_query($SQL5, $db);
			}
			// From mpo 
			if ($_POST['salespersonIGPType'] == "D") {
				$SQL5 = "SELECT DISTINCT stockmaster.stockid,
				stockmaster.description,
				stockmaster.mnfCode,
				stockmaster.longdescription,
				stockmaster.mnfCode,
				stockmaster.mnfpno,
				stockmaster.mbflag,
				stockmaster.discontinued,
				stockissuance.issued  AS qoh,
				stockmaster.units,
				stockmaster.decimalplaces,
				ogpmporef.mpo
					FROM stockmaster INNER JOIN stockissuance
					ON stockmaster.stockid=stockissuance.stockid
					INNER JOIN ogpmporef
					ON stockmaster.stockid=ogpmporef.stockid
					where stockissuance.salesperson = '" . $_SESSION['Request']->receivedfrom . "'
					AND ogpmporef.mpo = '" . $_SESSION['Request']->mpo . "'
					AND ogpmporef.quantity != ''
					AND ogpmporef.quantity != '0'
					and stockissuance.issued>0
					AND stockmaster.stockid NOT LIKE '%\t%'
					order by stockissuance.issued desc
					";

				$UpdateResult = DB_query($SQL5, $db);
			}
			// Fom Cart
			if ($_POST['salespersonIGPType'] == "E") {
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
					where stockissuance.salesperson = '" . $_SESSION['Request']->receivedfrom . "'
					and stockissuance.issued>0
					AND stockmaster.stockid NOT LIKE '%\t%'
					order by stockissuance.issued desc
					";
				$UpdateResult = DB_query($SQL5, $db);
			}
		}

		//unset($_POST['Update']);


		if (isset($_POST['Next'])) {
			$Offset = $_POST['NextList'];
		}
		if (isset($_POST['Prev'])) {
			$Offset = $_POST['Previous'];
		}
		if (!isset($Offset) or $Offset < 0) {
			$Offset = 0;
		}
		$SQL = $SQL5 . ' LIMIT ' . $DefaultDisplayRecordsMax . ' OFFSET ' . ($DefaultDisplayRecordsMax * $Offset);

		//$ErrMsg = _('There is a problem selecting the part records to display because');
		//$DbgMsg = _('The SQL used to get the part selection was');
		$UpdateResult = DB_query($SQL5, $db);

		if (DB_num_rows($UpdateResult) == 0) {
			prnMsg(_('There are no products available meeting the criteria specified'), 'info');
		}
		if (DB_num_rows($UpdateResult) < $_SESSION['DisplayRecordsMax']) {
			$Offset = 0;
		}

		//end of if Update
		/* display list if there is more than one record */
		if (isset($Updateresult) and !isset($_POST['Select'])) {
			echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST">';
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
				<input type="hidden" name=Keywords value="' . $_POST['Keywords'] . '" />
				<input type="hidden" name=StockCat value="' . $_POST['StockCat'] . '" />
				<input type="hidden" name=StockCode value="' . $_POST['StockCode'] . '" />
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
				while (($myrow = DB_fetch_array($Updateresult)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
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
					if ($myrow['discontinued'] == 1) {
						$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
					} else {
						$ItemStatus = '';
					}

					echo '<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
					<td>' . $myrow['description'] . '</td>
					<td>' . $myrow['mnfCode'] . '</td>
					<td class="number">' . $qoh . '</td>
					<td>' . $myrow['units'] . '</td>
					<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'] . '">' . _('View') . '</a></td>
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
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST" id="orderform">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table class="table1">
		<tr>
			<td>
				<input type="hidden" name="Previous" value="' . ($Offset - 1) . '" />
				<input tabindex="' . ($j + 8) . '" type="submit" name="Prev" value="' . _('Prev') . '" /></td>
				<td style="text-align:center" colspan="6">
				<input type="hidden" name="order_items" value="1" />
				<input tabindex="' . ($j + 9) . '" type="submit" value="' . _('Add to IGP') . '" /></td>
			<td>
				<input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
				<input tabindex="' . ($j + 10) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>
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

			$k = 0; //row colour counter
			$i = 0;
			$QOH = array();
			while ($myrow = DB_fetch_array($UpdateResult)) {
				$salescase = NULL;
				$csv = NULL;
				$crv = NULL;
				$mpo = NULL;
				$otherOGPTypeCount = NULL;
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

				if ($_POST['salespersonIGPType'] == "A") {
					$QOHSQL = "SELECT SUM(quantity) AS qoh
							   FROM ogpsalescaseref
							   WHERE ogpsalescaseref.stockid='" . $myrow['stockid'] . "' AND
							   ogpsalescaseref.salescaseref = '" . $_SESSION['Request']->salescaseref . "'";
				} elseif ($_POST['salespersonIGPType'] == "B") {
					$QOHSQL = "SELECT SUM(quantity) AS qoh
							   FROM ogpcsvref
							   WHERE ogpcsvref.stockid='" . $myrow['stockid'] . "' AND
							   ogpcsvref.csv = '" . $_SESSION['Request']->csv . "'";
				} elseif ($_POST['salespersonIGPType'] == "C") {
					$QOHSQL = "SELECT SUM(quantity) AS qoh
							   FROM ogpcrvref
							   WHERE ogpcrvref.stockid='" . $myrow['stockid'] . "' AND
							   ogpcrvref.crv = '" . $_SESSION['Request']->crv . "'";
				} elseif ($_POST['salespersonIGPType'] == "D") {
					$QOHSQL = "SELECT SUM(quantity) AS qoh
							   FROM ogpmporef
							   WHERE ogpmporef.stockid='" . $myrow['stockid'] . "' AND
							   ogpmporef.mpo = '" . $_SESSION['Request']->mpo . "'";
				} elseif ($_POST['salespersonIGPType'] == "E") {
					$QOHSQL = "SELECT SUM(quantity) AS qoh
							   FROM ogpsalescaseref
							   WHERE ogpsalescaseref.stockid='" . $myrow['stockid'] . "' AND
							   ogpsalescaseref.salesman = '" . $_SESSION['Request']->receivedfrom . "' AND
							   ogpsalescaseref.quantity > 0
							   ";
					$salescaseResult =  DB_query($QOHSQL, $db);
					$row = DB_fetch_array($salescaseResult);
					$salescase = $row['qoh'];
					// csv
					$QOHSQL = "SELECT SUM(quantity) AS qoh
							   FROM ogpcsvref
							   WHERE ogpcsvref.stockid='" . $myrow['stockid'] . "' AND
							   ogpcsvref.quantity > 0 AND
							   ogpcsvref.salesman = '" . $_SESSION['Request']->receivedfrom . "'";
					$salescaseResult =  DB_query($QOHSQL, $db);
					$row = DB_fetch_array($salescaseResult);
					$csv = $row['qoh'];
					// crv
					$QOHSQL = "SELECT SUM(quantity) AS qoh
							   FROM ogpcrvref
							   WHERE ogpcrvref.stockid='" . $myrow['stockid'] . "' AND
							   ogpcrvref.quantity > 0 AND
							   ogpcrvref.salesman = '" . $_SESSION['Request']->receivedfrom . "'";
					$salescaseResult =  DB_query($QOHSQL, $db);
					$row = DB_fetch_array($salescaseResult);
					$crv = $row['qoh'];
					// mpo
					$QOHSQL = "SELECT SUM(quantity) AS qoh
							   FROM ogpmporef
							   WHERE ogpmporef.stockid='" . $myrow['stockid'] . "' AND
							   ogpmporef.quantity > 0 AND
							   ogpmporef.salesman = '" . $_SESSION['Request']->receivedfrom . "'";
					$salescaseResult =  DB_query($QOHSQL, $db);
					$row = DB_fetch_array($salescaseResult);
					$mpo = $row['qoh'];

					$otherOGPTypeCount = $salescase + $csv + $crv + $mpo;
					$QOHSQL = "SELECT issued AS qoh
							   FROM stockissuance
							   WHERE stockissuance.stockid='" . $myrow['stockid'] . "' AND
							   stockissuance.salesperson = '" . $_SESSION['Request']->receivedfrom . "'";
				}
				$QOHResult =  DB_query($QOHSQL, $db);
				$QOHRow = DB_fetch_array($QOHResult);
				$q = 'Quantity' . $i;
				$p = 'ItemDescription' . $i;
				$QOH[$i] = $QOHRow['qoh'];
				for ($foo = 0; $foo <= count($_SESSION['Request']->LineItems); $foo++) {
					//echo $_SESSION['Request']->LineItems[$Index]->StockID;
					if ($_SESSION['Request']->LineItems[$foo]->ItemDescription == $_POST[$p]) {
						$QOH[$i] = $QOHRow['qoh'] - $_SESSION['Request']->LineItems[$foo]->Quantity - $_POST[$q];
					}
				}

				$QOH[$i] = $QOH[$i] - $_POST[$q];
				$QOH[$i] = $QOH[$i] - $otherOGPTypeCount;
				if ($QOH[$i] < 0) {
					$QOH[$i] = 0;
				}

				// Find the quantity on outstanding sales orders

				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				if ($QOH[$i] != 0) {
					$OnOrder = $PurchQty + $WoQty;
					$Available = $QOH[$i] - $DemandQty + $OnOrder;
					echo '<td><a href = "SelectProduct.php?Select=' . $myrow['stockid'] . '" target = "_blank" >' . $myrow['stockid'] . '</a></td>
				
				<td>' . $myrow['description'] . '</td>
				<td>' . $myrow['mnfCode'] . '</td>
				
				<td>' . $myrow['stockunits'] . '</td>
				<td class="number">' . locale_number_format($QOH[$i], $DecimalPlaces) . '</td>
			<td><input class="number" ' . ($i == 0 ? 'autofocus="autofocus"' : '') . ' tabindex="' . ($j + 7) . '" type="text" size="6" name="Quantity' . $i . '" value="0" />
				<td><input  tabindex="' . ($j + 7) . '" type="text" size="26" name="Comments' . $i . '" value=" " />
				
				
				<input type="hidden" name="StockID' . $i . '" value="' . $myrow['stockid'] . '" />
				</td>
			</tr>';
					echo '<input type="hidden" name="DecimalPlaces' . $i . '" value="' . $myrow['decimalplaces'] . '" />';
					echo '<input type="hidden" name="ItemDescription' . $i . '" value="' . $myrow['description'] . '" />';
					echo '<input type="hidden" name="Units' . $i . '" value="' . $myrow['stockunits'] . '" />';
					$i++;
				}
			}
			#end of while loop
			echo '<tr>
			<td><input type="hidden" name="Previous" value="' . ($Offset - 1) . '" />
				<input tabindex="' . ($j + 7) . '" type="submit" name="Prev" value="' . _('Prev') . '" /></td>
			<td style="text-align:center" colspan="6"><input type="hidden" name="order_items" value="1" />
				<input tabindex="' . ($j + 8) . '" type="submit" value="' . _('Add to IGP') . '" /></td>
			<td><input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
				<input tabindex="' . ($j + 9) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>
		<tr/>
		</table>
       </div>
       </form>';
		}
	} else {
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
					and locstock.loccode = '" . $_SESSION['UserStockLocation'] . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
					GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.longdescription,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.discontinued,
					stockmaster.decimalplaces
					ORDER BY locstock.quantity desc";
						echo $SQLA;
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
					and locstock.loccode = '" . $_SESSION['UserStockLocation'] . "'
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
						and locstock.loccode = '" . $_SESSION['UserStockLocation'] . "'
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
						and locstock.loccode = '" . $_SESSION['UserStockLocation'] . "'
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
					and locstock.loccode = '" . $_SESSION['UserStockLocation'] . "'
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
					and locstock.loccode = '" . $_SESSION['UserStockLocation'] . "'
					
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
					and locstock.loccode = '" . $_SESSION['UserStockLocation'] . "'
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
					where locstock.loccode = '" . $_SESSION['UserStockLocation'] . "'
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
				<input tabindex="' . ($j + 9) . '" type="submit" value="' . _('Add to IGP') . '" /></td>
			<td>
				<input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
				<input tabindex="' . ($j + 10) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>
			</tr>';

			echo '<tr>
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
				<input tabindex="' . ($j + 8) . '" type="submit" value="' . _('Add to IGP') . '" /></td>
			<td><input type="hidden" name="NextList" value="' . ($Offset + 1) . '" />
				<input tabindex="' . ($j + 9) . '" type="submit" name="Next" value="' . _('Next') . '" /></td>\
		<tr/>
			
		</table>
       </div>
       </form>';
		}
	} #end if SearchResults to show
	?>
</body>

</html>
<?php
//*********************************************************************************************************
include('includes/footer.inc');
