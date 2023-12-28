<?php

/* $Id: InternalStockRequest.php 4576 2011-05-27 10:59:20Z daintree $*/

include('includes/DefinePOSClass.php');
include('includes/session.inc');
$Title = _('Create a Stock Request');
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
if (isset($_POST['ogp']))

	$_SESSION['Request']->ogp = $_POST['ogp'];
$_POST['ogp'] = $_SESSION['Request']->ogp;

if (isset($_POST['salespersonOgpType'])) {
	$_SESSION['Request']->salespersonOgpType = $_POST['salespersonOgpType'];
	$_POST['salespersonOgpType'] = $_SESSION['Request']->salespersonOgpType;
}

if (isset($_POST['deliveredto'])) {

	$_SESSION['Request']->deliveredto = $_POST['deliveredto'];
}


if (isset($_POST['salescaseref']))
	$_SESSION['Request']->salescaseref = $_POST['salescaseref'];
$_POST['salescaseref'] = $_SESSION['Request']->salescaseref;

if (isset($_POST['parchino']))
	$_SESSION['Request']->parchino = $_POST['parchino'];
$_POST['parchino'] = $_SESSION['Request']->parchino;

if (isset($_POST['crv']))
	$_SESSION['Request']->crv = $_POST['crv'];
$_POST['crv'] = $_SESSION['Request']->crv;

if (isset($_POST['csv']))
	$_SESSION['Request']->csv = $_POST['csv'];
$_POST['csv'] = $_SESSION['Request']->csv;



if (isset($_POST['brand']))
	$_SESSION['brand'] = $_POST['brand'];
if (isset($_POST['StockCode']))
	$_SESSION['StockCode'] = $_POST['StockCode'];
if (isset($_POST['substore']))
	$_SESSION['substore'] = $_POST['substore'];


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

if (isset($_POST['Update'])) {
	$InputError = 0;

	if ($_POST['substore'] == '') {
		prnMsg(_('You must select a substore to request the items from'), 'error');
		$InputError = 1;
	}
	if ($InputError == 0) {
		$_SESSION['Request']->Location = $_SESSION['UserStockLocation'];
		$_SESSION['Request']->source = $_POST['source'];
		$_SESSION['Request']->reference = $_POST['reference'];

		$_SESSION['Request']->DispatchDate = $_POST['DispatchDate'];
		$_SESSION['Request']->Narrative = $_POST['deliveredto'];
		$_SESSION['Request']->storemanager = $_SESSION['UsersRealName'];
		$_SESSION['Request']->substore = $_POST['substore'];
		$_SESSION['Request']->deliveredto = $_POST['deliveredto'];
		$_SESSION['Request']->ogp = $_POST['ogp'];
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
	if (round($QtyOnHandPrior - $LineItems->Quantity, 0) < 0) {
		prnMsg(_('Negative quantity Not Allowed'), 'error');
		include('includes/footer.inc');
		exit;
	}
}
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

	if ($_SESSION['Request']->salespersonOgpType == '') {
		prnMsg(_('You must select a Salescaseperson OGP type as well'), 'error');
		$InputError = 1;
	}

	if ($_SESSION['Request']->salespersonOgpType == 'D') {
	$SQL = "select salescaseref,salescaseindex from salescase WHERE salesman = '" . $_SESSION['Request']->deliveredto . "' AND salescaseref = '" . $_SESSION['Request']->salescaseref . "'";
	$result = DB_query($SQL, $db);
	$matchFound = DB_num_rows($result) > 0 ? 'yes' : 'no';
	if ($matchFound == 'no') {
		prnMsg(_('You must select a Salescase reference as well and Update first'), 'error');
		$InputError = 1;
	}
}

	if ($InputError == 0) {
		$RequestNo = GetNextTransNo(38, $db);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

		$HeaderSQL = "INSERT INTO posdispatch (dispatchid,
											loccode,
											despatchdate,
											deliveredto,
											storemanager,											
											narrative
											)
										VALUES(
											'" . $RequestNo . "',
											
											'" . $_SESSION['Request']->Location . "',
											'" . FormatDateForSQL($_SESSION['Request']->DispatchDate) . "',
											
											'" . $_SESSION['Request']->deliveredto . "',
											'" . $_SESSION['Request']->storemanager . "',
											
											'" . $_SESSION['Request']->Narrative . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);

		if ($_SESSION['Request']->salescaseref != NULL) {
			$selectedItemsCode = NULL;
			foreach ($_SESSION['Request']->LineItems as $LineItems) {
				$itemcode = "SELECT * FROM ogpsalescaseref WHERE salescaseref= '" . $_SESSION['Request']->salescaseref . "'	
					AND stockid = '" . $LineItems->StockID . "' AND salesman = '" . $_SESSION['Request']->deliveredto . "'";
				$Result = DB_query($itemcode, $db);

				if (DB_num_rows($Result) == 1) {
					$itemcode = "UPDATE ogpsalescaseref SET quantity =quantity +'" . $LineItems->Quantity . "' WHERE salescaseref= '" . $_SESSION['Request']->salescaseref . "'
							AND stockid = '" . $LineItems->StockID . "' AND  salesman = '" . $_SESSION['Request']->deliveredto . "'";
					$Result = DB_query($itemcode, $db);
				} else {

					$HeaderSalescaserefSQL = "INSERT INTO ogpsalescaseref (dispatchid,
												salescaseref,
												requestedby,
												stockid,
												salesman,
												quantity
												)
											VALUES (
												'" . $RequestNo . "',
												'" . $_SESSION['Request']->salescaseref . "',
												'" . $_SESSION['UsersRealName'] . "',
												'" . $LineItems->StockID . "',
												'" . $_SESSION['Request']->deliveredto . "',
												'" . $LineItems->Quantity . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
					$DbgMsg = _('The following SQL to insert the request header record was used');
					$Result = DB_query($HeaderSalescaserefSQL, $db, $ErrMsg, $DbgMsg, true);
				}
			}
		}

		if ($_SESSION['Request']->parchino) {
			$selectedItemsCode = NULL;
			foreach ($_SESSION['Request']->LineItems as $LineItems) {
				$itemcode = "SELECT * FROM ogpmporef WHERE mpo= '" . $_SESSION['Request']->parchino . "'";
				$Result = DB_query($itemcode, $db);

				$HeaderSalescaserefSQL = "INSERT INTO ogpmporef (dispatchid,
												mpo,
												requestedby,
												stockid,
												salesman,
												quantity
												)
											VALUES (
												'" . $RequestNo . "',
												'" . $_SESSION['Request']->parchino . "',
												'" . $_SESSION['UsersRealName'] . "',
												'" . $LineItems->StockID . "',
												'" . $_SESSION['Request']->deliveredto . "',
												'" . $LineItems->Quantity . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSalescaserefSQL, $db, $ErrMsg, $DbgMsg, true);
			}
		}

		if ($_SESSION['Request']->csv) {
			$selectedItemsCode = NULL;
			foreach ($_SESSION['Request']->LineItems as $LineItems) {

				$itemcode = "SELECT * FROM ogpcsvref WHERE csv= '" . $_SESSION['Request']->csv . "'";
				$Result = DB_query($itemcode, $db);

				$HeaderSalescaserefSQL = "INSERT INTO ogpcsvref (dispatchid,
											csv,
											requestedby,
											stockid,
											salesman,
											quantity
											)
										VALUES (
											'" . $RequestNo . "',
											'" . $_SESSION['Request']->csv . "',
											'" . $_SESSION['UsersRealName'] . "',
											'" . $LineItems->StockID . "',
											'" . $_SESSION['Request']->deliveredto . "',
											'" . $LineItems->Quantity . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSalescaserefSQL, $db, $ErrMsg, $DbgMsg, true);
			}
		}

		if ($_SESSION['Request']->crv) {
			$selectedItemsCode = NULL;
			foreach ($_SESSION['Request']->LineItems as $LineItems) {

				$itemcode = "SELECT * FROM ogpcrvref WHERE crv= '" . $_SESSION['Request']->crv . "'";
				$Result = DB_query($itemcode, $db);

				$HeaderSalescaserefSQL = "INSERT INTO ogpcrvref (dispatchid,
											crv,
											requestedby,
											stockid,
											salesman,
											quantity
											)
										VALUES (
											'" . $RequestNo . "',
											'" . $_SESSION['Request']->crv . "',
											'" . $_SESSION['UsersRealName'] . "',
											'" . $LineItems->StockID . "',
												'" . $_SESSION['Request']->deliveredto . "',
											'" . $LineItems->Quantity . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSalescaserefSQL, $db, $ErrMsg, $DbgMsg, true);
			}
		}


		// foreach ($_SESSION['Request']->LineItems as $LineItems) {
		// 	$itemcode = "SELECT stockid FROM ogpsalescaseref WHERE salescaseref= '" . $_SESSION['Request']->salescaseref . "'";
		// 	$Result = DB_query($itemcode, $db);

		// 	if (DB_num_rows($Result) == 1) {
		// 		$Stockid = DB_fetch_row($Result);
		// 		$selectedItems = $Stockid + "," + $LineItems->StockID;
		// 		$itemcode = "UPDATE ogpsalescaseref SET stockid ='$selectedItems' WHERE salescaseref= '" . $_SESSION['Request']->salescaseref . "'";
		// 		$Result = DB_query($itemcode, $db);
		// 	}
		// }

		foreach ($_SESSION['Request']->LineItems as $LineItems) {
			$LineSQL = "INSERT INTO posdispatchitems (dispatchitemsid,
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
			$Result = DB_query($LineSQL, $db);
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
						511,
						'" . $RequestNo . "',
						'" . $_SESSION['Request']->Location . "',
							'" . FormatDateForSQL($_SESSION['Request']->DispatchDate) . "',
						'" . _('To') . ' ' . DB_escape_string($_SESSION['Request']->deliveredto) . "'
						,'" . round($LineItems->Quantity, 0) . "'
						,'" . $PeriodNo . "'
						
						,'" . round($QtyOnHandPrior - $LineItems->Quantity, 0) . "'
						)";

			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the incoming stock cannot be added because');
			$DbgMsg =  _('The following SQL to insert the stock movement record was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);



			if ($_SESSION['Request']->ogp == "A") {
				$SQL = "select stockid, salesperson from stockissuance where stockid = '" . $LineItems->StockID . "'
	
	and salesperson = '" . $_SESSION['Request']->deliveredto . "'
	
	";
				$Result = DB_query($SQL, $db);
				if (mysqli_num_rows($Result) > 0) {
					$SQL = "UPDATE stockissuance
					SET issued = issued + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND salesperson='" . $_SESSION['Request']->deliveredto . "'";
					$Result = DB_query($SQL, $db);
				} else {

					$SQL = "insert into stockissuance(salesperson,stockid,issued) values
					('" . $_SESSION['Request']->deliveredto . "','" . $LineItems->StockID . "'
					,'" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "')";
					$Result = DB_query($SQL, $db);
				}


				$SQL = "UPDATE locstock
					SET quantity = quantity - '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND loccode='" . $_SESSION['Request']->Location . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE substorestock
					SET quantity = quantity - '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND substoreid='" .  $_SESSION['Request']->substore . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
			}
			if ($_SESSION['Request']->ogp == "E") {
				$SQL = "select stockid, engineer from workorderissuance where stockid = '" . $LineItems->StockID . "'
	
	and engineer = '" . $_SESSION['Request']->deliveredto . "'
	
	";
				$Result = DB_query($SQL, $db);
				if (mysqli_num_rows($Result) > 0) {
					$SQL = "UPDATE workorderissuance
					SET issued = issued + '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND engineer='" . $_SESSION['Request']->deliveredto . "'";
					$Result = DB_query($SQL, $db);
				} else {

					$SQL = "insert into workorderissuance(engineer,stockid,issued) values
					('" . $_SESSION['Request']->deliveredto . "','" . $LineItems->StockID . "'
					,'" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "')";
					$Result = DB_query($SQL, $db);
				}


				$SQL = "UPDATE locstock
					SET quantity = quantity - '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND loccode='" . $_SESSION['Request']->Location . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE substorestock
					SET quantity = quantity - '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND substoreid='" .  $_SESSION['Request']->substore . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
			}

			if ($_SESSION['Request']->ogp == "B" or $_SESSION['Request']->ogp == "C") {

				$SQL = "UPDATE locstock
					SET quantity = quantity - '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND loccode='" . $_SESSION['Request']->Location . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE substorestock
					SET quantity = quantity - '" . round($LineItems->Quantity, $LineItems->DecimalPlaces) . "'
					WHERE stockid='" . $LineItems->StockID . "'
					AND substoreid='" . $_SESSION['Request']->substore . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
			}
		}
		DB_Txn_Commit($db);
		echo '<p><a href="' . $RootPath . '/PDFOGP.php?RequestNo=' . $RequestNo . '">' .  _('Print the OGP') . '</a></p>';

		prnMsg(_('The internal stock request has been entered and now needs to be authorized'), 'success');
		echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?New=Yes">' . _('Create another request') . '</a></div>';
		include('includes/footer.inc');
		unset($_SESSION['Request']);
		exit;
	}
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
	echo '<form name = "formA" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="POST">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';
	echo '<tr>
		<th colspan="2"><h4>' . _('Outwards Gate Pass') . '</h4></th>
	</tr>';

	echo '	<tr>
		<td>' . _('Select OGP Type ') . ':</td>';
	echo '<td><select name="ogp" onchange="ReloadForm(formA.UpdateForm)">';

	echo '<option value="">' . _('Select OGP Type') . '</option>';
	if (isset($_POST['ogp']) and $_POST['ogp'] == "A")

		echo '<option value="A" selected = "selected">' . _('Issue to Sales Person') . '</option>';
	else echo '<option value="A">' . _('Issue to Sales Person') . '</option>';

	if (isset($_POST['ogp']) and $_POST['ogp'] == "D")

		echo '<option value="D" selected = "selected">' . _('Issue to Employee') . '</option>';
	else echo '<option value="D">' . _('Issue to Employee') . '</option>';

	// if (isset($_POST['ogp']) and $_POST['ogp'] == "E")

	// 	echo '<option value="E" selected = "selected">' . _('Issue to Engineer') . '</option>';
	// else echo '<option value="E">' . _('Issue to Engineer') . '</option>';


	if (isset($_POST['ogp']) and $_POST['ogp'] == "B")

		echo '<option value="B" selected = "selected">' . _('Deliver to store location') . '</option>';
	else echo '<option value="B">' . _('Delivered to store location') . '</option>';

	if (isset($_POST['ogp']) and $_POST['ogp'] == "C")
		echo '<option value="C" selected = "selected">' . _('External destination') . '</option>';


	else echo '<option value="C">' . _('External destination') . '</option>';



	echo '</select></td>
	<tr>';
	if (isset($_SESSION['Request']->ogp) and $_SESSION['Request']->ogp == "A") {


		echo '
		<td>' . _('Issued to Sales Person ') . ':</td>';
		$sql = "select salesmanname from salesman";
		$result = DB_query($sql, $db);
		echo '<td><select name="deliveredto" onchange="ReloadForm(formA.UpdateForm)" >
<option value="">' . _('Select a Sales Person') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->deliveredto) and $_SESSION['Request']->deliveredto == $myrow['salesmanname']) {
				echo '<option selected="True" value="' . $myrow['salesmanname'] . '">' . htmlspecialchars($myrow['salesmanname'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['salesmanname'] . '">' . htmlspecialchars($myrow['salesmanname'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}


		echo '</select></td> </tr>';



		echo '	
				<td>' . _('Salesperson OGP Type ') . ':</td>';
		echo '<td><select name="salespersonOgpType" onchange="ReloadForm(formA.UpdateForm)">';

		echo '<option value="">' . _('Salesperson OGP Type') . '</option>';
		if ((isset($_POST['salespersonOgpType']) and $_POST['salespersonOgpType'] == "A") || $_SESSION['Request']->salespersonOgpType == 'A')

			echo '<option value="A" selected = "selected">' . _('CSV') . '</option>';
		else echo '<option value="A">' . _('CSV') . '</option>';

		if ((isset($_POST['salespersonOgpType']) and $_POST['salespersonOgpType'] == "B") || $_SESSION['Request']->salespersonOgpType == 'B')

			echo '<option value="B" selected = "selected">' . _('CRV') . '</option>';
		else echo '<option value="B">' . _('CRV') . '</option>';


		if ((isset($_POST['salespersonOgpType']) and $_POST['salespersonOgpType'] == "C") || $_SESSION['Request']->salespersonOgpType == 'C')

			echo '<option value="C" selected = "selected">' . _('MPO') . '</option>';
		else echo '<option value="C">' . _('MPO') . '</option>';

		if ((isset($_POST['salespersonOgpType']) and $_POST['salespersonOgpType'] == "D") || $_SESSION['Request']->salespersonOgpType == 'D')
			echo '<option value="D" selected = "selected">' . _('Salescase') . '</option>';
		else echo '<option value="D">' . _('Salescase') . '</option>';

		if ((isset($_POST['salespersonOgpType']) and $_POST['salespersonOgpType'] == "E") || $_SESSION['Request']->salespersonOgpType == 'E')
			echo '<option value="E" selected = "selected">' . _('Cart') . '</option>';
		else echo '<option value="E">' . _('Cart') . '</option>';

		echo '</select></td>';
		echo '<tr>';
		if (isset($_SESSION['Request']->salespersonOgpType) and $_SESSION['Request']->salespersonOgpType == "D") {


			echo '
		<td>' . _('Issue agaist salescase ') . ':</td>';
			$sql = "select salescaseref,salescaseindex from salescase WHERE salesman = '" . $_SESSION['Request']->deliveredto . "' AND closed = '0' ";
			$result = DB_query($sql, $db);
			echo '<td><select class="js-example-basic-single" name="salescaseref" onchange="ReloadForm(formA.UpdateForm)">';
			if (!isset($_SESSION['Request']->salescaseref)) {
				echo '<option value="">' . _('Select a Salescase') . '</option>';
			}
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

		if (isset($_SESSION['Request']->salespersonOgpType) and $_SESSION['Request']->salespersonOgpType == "C") {


			echo '
		<td>' . _('Issue agaist MPO ') . ':</td>';
			$sql = "SELECT * FROM `bazar_parchi` WHERE `on_behalf_of` = '" . $_SESSION['Request']->deliveredto . "' AND `inprogress` = '1'";
			$result = DB_query($sql, $db);
			echo '<td><select class="js-example-basic-single" name="parchino">
<option value="">' . _('Select a Parchi No') . '</option>';
			while ($myrow = DB_fetch_array($result)) {
				if (isset($_SESSION['Request']->salescaseref) and $_SESSION['Request']->salescaseref == $myrow['parchino']) {
					echo '<option selected="True" value="' . $myrow['parchino'] . '">' . htmlspecialchars($myrow['parchino'], ENT_QUOTES, 'UTF-8') . '</option>';
				} else {
					echo '<option value="' . $myrow['parchino'] . '">' . htmlspecialchars($myrow['parchino'], ENT_QUOTES, 'UTF-8') . '</option>';
				}
			}


			echo '</select></td>';
		}
		echo ' </tr>';

		echo '<tr>';
		if (isset($_SESSION['Request']->salespersonOgpType) and $_SESSION['Request']->salespersonOgpType == "B") {


			echo '
		<td>' . _('Issue Against CRV ') . ':</td>';
			$sql = "select orderno from shopsale WHERE salesman = '" . $_SESSION['Request']->deliveredto . "' AND 
				payment = 'crv' AND complete = '0'";
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

		echo '<tr>';
		if (isset($_SESSION['Request']->salespersonOgpType) and $_SESSION['Request']->salespersonOgpType == "A") {


			echo '
		<td>' . _('Issue Against CSV ') . ':</td>';
			$sql = "select orderno from shopsale WHERE salesman = '" . $_SESSION['Request']->deliveredto . "' AND 
				payment = 'csv'  AND complete = '0'";
			$result = DB_query($sql, $db);
			echo '<td><select class="js-example-basic-single" name="csv">
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
	}





	if (isset($_SESSION['Request']->ogp) and $_SESSION['Request']->ogp == "D") {


		echo '
		<td>' . _('Issued to Employee ') . ':</td>';
		$sql = "select realname from www_users";
		$result = DB_query($sql, $db);
		echo '<td><select name="deliveredto">
<option value="">' . _('Select an Employee') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->deliveredto) and $_SESSION['Request']->deliveredto == $myrow['realname']) {
				echo '<option selected="True" value="' . $myrow['realname'] . '">' . htmlspecialchars($myrow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['realname'] . '">' . htmlspecialchars($myrow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}


		echo '</select></td></tr>';
	}

	if (isset($_SESSION['Request']->ogp) and $_SESSION['Request']->ogp == "E") {


		echo '
		<td>' . _('Issued to Engineer ') . ':</td>';
		$sql = "select realname from www_users where defaultlocation = '" . $_SESSION['UserStockLocation'] . "' and fullaccess = 24";
		$result = DB_query($sql, $db);
		echo '<td><select name="deliveredto">
<option value="">' . _('Select an Engineer') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->deliveredto) and $_SESSION['Request']->deliveredto == $myrow['realname']) {
				echo '<option selected="True" value="' . $myrow['realname'] . '">' . htmlspecialchars($myrow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['realname'] . '">' . htmlspecialchars($myrow['realname'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}


		echo '</select></td></tr>';
	}

	if (isset($_SESSION['Request']->ogp) and $_SESSION['Request']->ogp == "B") {
		$_SESSION['Request']->ogp = $_POST['ogp'];

		echo	'<tr>
		<td>' . _('To Stock Location') . ':</td>';
		$sql = "SELECT loccode,
			locationname
		FROM locations
			
		";

		$result = DB_query($sql, $db);
		echo '<td><select name="deliveredto">
		<option value="">' . _('Select a location') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_SESSION['Request']->deliveredto) and $_SESSION['Request']->deliveredto == $myrow['locationname']) {
				echo '<option selected="True" value="' . $myrow['locationname'] . '">' . $myrow['loccode'] . ' - ' . htmlspecialchars($myrow['locationname'], ENT_QUOTES, 'UTF-8') . '</option>';
			} else {
				echo '<option value="' . $myrow['locationname'] . '">' . $myrow['loccode'] . ' - ' . htmlspecialchars($myrow['locationname'], ENT_QUOTES, 'UTF-8') . '</option>';
			}
		}
		echo '</select></td></tr>
	';
	}
	if (isset($_SESSION['Request']->ogp) and $_SESSION['Request']->ogp == "C") {


		echo '<tr>
		<td>' . _('External Destination') . ':</td>
		<td><textarea required name="deliveredto" cols="30" rows="5">' . $_SESSION['Request']->Narrative . '</textarea></td>
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
		<td>' . _('Date ') . ':</td>';
	echo '<td><input type="text" required = "required" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="DispatchDate" maxlength="10" size="11" value="' . $_SESSION['Request']->DispatchDate . '" /></td>
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

	$selectedItems = null;
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
			$QOHSQL = "SELECT sum(substorestock.quantity) AS qoh
							   FROM substorestock
							   WHERE substorestock.stockid='" . $myrow['stockid'] . "' AND
							   loccode = '" . $_SESSION['Request']->Location . "'
							    AND
							   substoreid = " . $_SESSION['substore'];
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
				<td class="number">' . $QOH . '</td>
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

	?>

</body>

</html>
//*********************************************************************************************************
<?php
include('includes/footer.inc');
