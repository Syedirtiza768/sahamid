<?php

/* $Id: InternalStockRequestFulfill.php  $*/

include('includes/session.inc');

$Title = _('Fulfill Stock Requests');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Contract') . '" alt="" />' . _('Fulfill Stock Requests') . '</p>';

if (isset($_POST['UpdateAll'])) {
	foreach ($_POST as $key => $value) {
		if (mb_strpos($key, 'Qty')) {
			$RequestID = mb_substr($key, 0, mb_strpos($key, 'Qty'));
			$LineID = mb_substr($key, mb_strpos($key, 'Qty') + 3);
			$Quantity = filter_number_format($_POST[$RequestID . 'Qty' . $LineID]);
			$StockID = $_POST[$RequestID . 'StockID' . $LineID];
			$Location = $_POST[$RequestID . 'Location' . $LineID];
			$Department = $_POST[$RequestID . 'Department' . $LineID];
			$Tag = $_POST[$RequestID . 'Tag' . $LineID];
			$RequestedQuantity = filter_number_format($_POST[$RequestID . 'RequestedQuantity' . $LineID]);
			if (isset($_POST[$RequestID . 'Completed' . $LineID])) {
				$Completed = True;
			} else {
				$Completed = False;
			}

			$sql = "SELECT materialcost, labourcost, overheadcost, decimalplaces FROM stockmaster WHERE stockid='" . $StockID . "'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
			$StandardCost = $myrow['materialcost'] + $myrow['labourcost'] + $myrow['overheadcost'];
			$DecimalPlaces = $myrow['decimalplaces'];

			$Narrative = _('Issue') . ' ' . $Quantity . ' ' . _('of') . ' ' . $StockID . ' ' . _('to department') . ' ' . $Department . ' ' . _('from') . ' ' . $Location;

			$AdjustmentNumber = GetNextTransNo(17, $db);
			$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
			$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

			$Result = DB_Txn_Begin($db);

			// Need to get the current location quantity will need it later for the stock movement
			$SQL = "SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $StockID . "'
						AND loccode= '" . $Location . "'";
			$Result = DB_query($SQL, $db);
			if (DB_num_rows($Result) == 1) {
				$LocQtyRow = DB_fetch_row($Result);
				$QtyOnHandPrior = $LocQtyRow[0];
			} else {
				// There must actually be some error this should never happen
				$QtyOnHandPrior = 0;
			}

			if ($_SESSION['ProhibitNegativeStock'] == 0 or ($_SESSION['ProhibitNegativeStock'] == 1 and $QtyOnHandPrior >= $Quantity)) {

				/*		
				$SQL = "INSERT INTO stockmoves (
									stockid,
									type,
									transno,
									shiploc,
									trandate,
									prd,
									reference,
									qty,
									newqoh)
								VALUES (
									'" . $StockID . "',
									17,
									'" . $AdjustmentNumber . "',
									'" . $Location . "',
									'" . $SQLAdjustmentDate . "',
									'" . $PeriodNo . "',
									'" . $Narrative ."',
									'" . -$Quantity . "',
									'" . ($QtyOnHandPrior - $Quantity) . "'
								)";

*/
				//			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
				//			$DbgMsg =  _('The following SQL to insert the stock movement record was used');
				//			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				for ($i = 0; $i < count($LineRow); $i++) {

					if ($_POST['StockID' . $i] != '') {
						$DecimalsSql = "SELECT decimalplaces
							FROM stockmaster
							WHERE stockid='" . $_POST['StockID' . $i] . "'";
						$DecimalResult = DB_Query($DecimalsSql, $db);
						$DecimalRow = DB_fetch_array($DecimalResult);
						$sql = "INSERT INTO loctransfers (reference,
								stockid,
								shipqty,
								shipdate,
								shiploc,
								recloc)
						VALUES ('" . $_POST['Trf_ID'] . "',
							'" . $_POST['StockID' . $i] . "',
							'" . round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) . "',
							'" . Date('Y-m-d H-i-s') . "',
							'" . $_POST['FromStockLocation']  . "',
							'" . $_POST['ToStockLocation'] . "')";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Location Transfer record for') . ' ' . $_POST['StockID' . $i];
						$resultLocShip = DB_query($sql, $db, $ErrMsg);
						/*$SQL = "UPDATE locstock
					SET quantity = quantity - '" . round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) ."'
					WHERE stockid='" . $_POST['StockID' . $i] ."'
					AND shiploc='" . $_POST['FromStockLocation']  ."'";
					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				*/
					}
				}
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to COMMIT Location Transfer transaction');
				DB_Txn_Commit($db);

				prnMsg(_('The inventory transfer records have been created successfully'), 'success');



				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');

				/*	$SQL="UPDATE stockrequestitems
						SET qtydelivered=qtydelivered+" . $Quantity . "
						WHERE dispatchid='" . $RequestID . "'
							AND dispatchitemsid='" . $LineID . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);

				$SQL = "UPDATE locstock SET quantity = quantity - '" . $Quantity . "'
									WHERE stockid='" . $StockID . "'
										AND shiploc='" . $Location . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');

				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
*/
				if ($_SESSION['CompanyRecord']['gllink_stock'] == 1 and $StandardCost > 0) {

					$StockGLCodes = GetStockGLCode($StockID, $db);

					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												amount,
												narrative,
												tag)
											VALUES (17,
												'"  . $AdjustmentNumber . "',
												'" . $SQLAdjustmentDate . "',
												'" . $PeriodNo . "',
												'" . $StockGLCodes['issueglact'] . "',
												'" . $StandardCost * ($Quantity) . "',
												'" . $Narrative . "',
												'" . $Tag . "'
											)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
					$DbgMsg = _('The following SQL to insert the GL entries was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												amount,
												narrative,
												tag)
											VALUES (17,
												'" . $AdjustmentNumber . "',
												'" . $SQLAdjustmentDate . "',
												'" . $PeriodNo . "',
												'" . $StockGLCodes['stockact'] . "',
												'" . $StandardCost * -$Quantity . "',
												'" . $Narrative . "',
												'" . $Tag . "'
											)";

					$Errmsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
					$DbgMsg = _('The following SQL to insert the GL entries was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				}

				if (($Quantity >= $RequestedQuantity) or $Completed == True) {
					$SQL = "UPDATE stockrequestitems
								SET completed=1
							WHERE dispatchid='" . $RequestID . "'
								AND dispatchitemsid='" . $LineID . "'";
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				}

				$Result = DB_Txn_Commit($db);

				$ConfirmationText = _('An internal stock request for') . ' ' . $StockID . ' ' . _('has been fulfilled from location') . ' ' . $Location . ' ' . _('for a quantity of') . ' ' . locale_number_format($Quantity, $DecimalPlaces);
				prnMsg($ConfirmationText, 'success');

				if ($_SESSION['InventoryManagerEmail'] != '') {
					$ConfirmationText = $ConfirmationText . ' ' . _('by user') . ' ' . $_SESSION['UserID'] . ' ' . _('at') . ' ' . Date('Y-m-d H:i:s');
					$EmailSubject = _('Internal Stock Request Fulfillment for') . ' ' . $StockID;
					if ($_SESSION['SmtpSetting'] == 0) {
						mail($_SESSION['InventoryManagerEmail'], $EmailSubject, $ConfirmationText);
					} else {
						include('includes/htmlMimeMail.php');
						$mail = new htmlMimeMail();
						$mail->setSubject($EmailSubject);
						$mail->setText($ConfirmationText);
						$result = SendmailBySmtp($mail, array($_SESSION['InventoryManagerEmail']));
					}
				}
			} else {
				$ConfirmationText = _('An internal stock request for') . ' ' . $StockID . ' ' . _('has been fulfilled from location') . ' ' . $Location . ' ' . _('for a quantity of') . ' ' . locale_number_format($Quantity, $DecimalPlaces) . ' ' . _('cannot be created as there is insufficient stock and your system is configured to not allow negative stocks');
				prnMsg($ConfirmationText, 'warn');
			}

			// Check if request can be closed and close if done.

			if (isset($RequestID)) {
				$SQL = "SELECT dispatchid
						FROM stockrequestitems
						WHERE dispatchid='" . $RequestID . "'
							AND completed=0";
				$Result = DB_query($SQL, $db);
				if (DB_num_rows($Result) == 0) {
					$SQL = "UPDATE stockrequest
						SET closed=1
					WHERE dispatchid='" . $RequestID . "'";
					$Result = DB_query($SQL, $db);
				}
			}
		}
	}
}

/*if (!isset($_POST['Location'])) {
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<tr>
		<td>' . _('From Stock Location').':</td>
		<td><select name="StockLocationFrom">';

//$sql = "select defaultlocation from ";
		//$sql = "SELECT substoreid, description FROM substores ";
		echo $sql = "SELECT substoreid, description FROM substores where locid like '".$_SESSION['UserStockLocation']."'";
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_SESSION['Transfer']->StockLocationFrom)){
		if ($myrow['shiploc'] == $_SESSION['Transfer']->StockLocationFrom){
			 echo '<option selected="selected" value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
		} else {
			 echo '<option value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
		}
	} elseif ($myrow['substoreid']==$_SESSION['UserStockLocation']){
		echo '<option selected="selected" value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
		if(isset($_SESSION['Transfer']))
		 $_SESSION['Transfer']->StockLocationFrom=$myrow['substoreid'];
	} else {
		 echo '<option value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
	}
}

echo '</select></td>
	</tr>';

	echo '</table><br />';
	echo '<div class="centre"><input type="submit" name="EnterAdjustment" value="'. _('Show Requests'). '" /></div>';
    echo '</div>
          </form>';
	include('includes/footer.inc');
	exit;
}
$c = $_POST['slct'];
echo "$c";
/* Retrieve the requisition header information
 
if (isset($_POST['Location'])) {
*/
$sql = "SELECT stockrequest.dispatchid,
			locations.locationname,
			stockrequest.requestdate as dispatchdate,
			stockrequest.narrative,
			stockrequest.authorizer,
			stockrequest.recloc,
			stockrequest.shiploc,
			www_users.realname,
			www_users.email
		FROM stockrequest
		LEFT JOIN locations
			ON stockrequest.shiploc=loccode
		LEFT JOIN www_users
			ON www_users.realname = stockrequest.authorizer
	WHERE stockrequest.authorised=1
		AND stockrequest.closed=0
	";
$result = DB_query($sql, $db);

if (DB_num_rows($result) == 0) {
	prnMsg(_('There are no outstanding authorised requests for this location'), 'info');
	echo '<br />';
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Select another location') . '</a></div>';
	include('includes/footer.inc');
	exit;
}

echo '<form method="POST" action="StockLocTransferRequested.php">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">
			<tr>
				<th>' . _('Select') . '</th>
				<th>' . _('Request Number') . '</th>
				<th>' . _('Request by') . '</th>
				<th>' . _('Location Of Stock') . '</th>
				<th>' . _('Requested Date') . '</th>
				<th>' . _('Narrative') . '</th>
			</tr>';
$maxdispatchitemid = array();
while ($myrow = DB_fetch_array($result)) {
	$slctdispatchid = $myrow['dispatchid'];

	$sqli = "SELECT * FROM ogpsalescaseref WHERE dispatchid =" . $myrow['dispatchid']. " ";
	$resulti = DB_query($sqli, $db);
	if (DB_num_rows($resulti) != 0) {
		$query2 = DB_fetch_array($resulti);
		$requestedby = $query2['salescaseref'];
	}

	echo '<tr>
				<td><input type = "radio" required = "required" name = "slct" value = ' . $myrow['dispatchid'] . '></td>
				<td>' . $myrow['dispatchid'] . '</td>
				<td>' . $myrow['authorizer'] . '(' . $myrow['recloc'] . ')&nbsp&nbsp&nbsp  ' .$requestedby. '</td>
				<td>' . $myrow['locationname'] . '</td>
				<td>' . ConvertSQLDate($myrow['dispatchdate']) . '</td>
				<td>' . $myrow['narrative'] . '</td>
			<td>	<input type="hidden" name="authorizer' . $myrow['dispatchid'] . '" value="' . $myrow['authorizer'] . '" /></td>
			<td>	<input type="hidden" name="fromloc' . $myrow['dispatchid'] . '" value="' . $myrow['shiploc'] . '" /></td>
			<td>	<input type="hidden" name="toloc' . $myrow['dispatchid'] . '" value="' . $myrow['recloc'] . '" /></td>
			
			</tr>';


	$LineSQL = "SELECT stockrequestitems.dispatchitemsid,
						stockrequestitems.dispatchid,
						stockrequestitems.stockid,
						stockrequestitems.decimalplaces,
						stockrequestitems.uom,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						
						stockmaster.description,
						
						stockrequestitems.quantity,
						stockrequestitems.comments,
						
						stockrequestitems.qtydelivered
				FROM stockrequestitems
				LEFT JOIN stockmaster
				ON stockmaster.stockid=stockrequestitems.stockid
			WHERE dispatchid='" . $myrow['dispatchid'] . "'
				AND completed=0
				
				
				";
	$LineResult = DB_query($LineSQL, $db);


	while ($LineRow = DB_fetch_array($LineResult)) {

		echo '<tr>
				<td></td>
				<td colspan="5" align="left">
					<table class="selection" align="left">
					<tr>
						<th>' . _('Model No.') . '</th>
						<th>' . _('Quantity') . '<br />' . _('Required') . '</th>
						<th>' . _('Quantity') . '<br />' . _('Delivered') . '</th>
							<th>' . _('Comments') . '</th>
					
						<th>' . _('Units') . '</th>
						<th>' . _('Completed') . '</th>
						
					</tr>';
		echo '<tr>
					<td>' . $LineRow['mnfCode'] . '</td>
					<td class="number">' . locale_number_format($LineRow['quantity'] - $LineRow['qtydelivered'], $LineRow['decimalplaces']) . '</td>
					<td class="number"><input type="number" required = "required" class="number" name="' . $LineRow['dispatchid'] . 'Qty' . $LineRow['dispatchitemsid'] . '" value= 0 /></td>
					<td>' . $LineRow['comments'] . '</td>
					
					<td>' . $LineRow['uom'] . '</td>
					<td><input type="checkbox" name="' . $LineRow['dispatchid'] . 'Completed' . $LineRow['dispatchitemsid'] . '" /></td>';
		$maxdispatchitemid[$slctdispatchid] = locale_number_format($LineRow['dispatchitemsid'], $LineRow['decimalplaces']);

		echo '<input type="hidden" class="number" name="' . $LineRow['dispatchid'] . 'StockID' . $LineRow['dispatchitemsid'] . '" value="' . $LineRow['stockid'] . '" />';
		echo '<input type="hidden" class="number" name="' . $LineRow['dispatchid'] . 'RequestedQuantity' . $LineRow['dispatchitemsid'] . '" value="' . locale_number_format($LineRow['quantity'] - $LineRow['qtydelivered'], $LineRow['decimalplaces']) . '" />';
		echo '<input type="hidden" class="number" name="' . $LineRow['dispatchid'] . 'Department' . $LineRow['dispatchitemsid'] . '" value="' . $myrow['description'] . '" />';

		//$maxdispatchid = locale_number_format($LineRow['dispatchid'],$LineRow['decimalplaces']);
		//	}
		$sql = "SELECT substores.locid,
				substores.description,
				substorestock.quantity,
				substorestock.stockid
		FROM substores INNER JOIN substorestock
		ON substores.substoreid=substorestock.substoreid
		WHERE substorestock.stockid = '" . $LineRow['stockid'] . "'
		and substorestock.loccode = '" . $_SESSION['UserStockLocation'] . "'
		";

		$ErrMsg = _('The stock held at each location cannot be retrieved because');
		$DbgMsg = _('The SQL that was used to update the stock item and failed was');
		$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
		echo '<br /><tr><td>';


		$j = 1;
		$k = 0; //row colour counter

		while ($myrow = DB_fetch_array($LocStockResult)) {

			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}


			printf(
				'<td class="number">%s</td>
				<td class="number">%s</td>
				',
				$myrow['description'],
				locale_number_format($myrow['quantity'], $DecimalPlaces)

			);
		}
		//end of page full new headings if
		echo '</table>';
	} // end while order line detail
	echo '</td></tr>';


	echo '<input type="hidden" class="number" name="' . $slctdispatchid . 'maxdispatchitemid" value="' . locale_number_format($maxdispatchitemid[$slctdispatchid], $LineRow['decimalplaces']) . '" />';
} //end while header loop
//$maxdispatchitemid = locale_number_format($LineRow['dispatchitemsid'],$LineRow['decimalplaces']);

$maxdispatchid  = locale_number_format($_POST['slct'], $LineRow['decimalplaces']);

echo '<input type="hidden" class="number" name="maxdispatchid" value="' . locale_number_format($maxdispatchid, $LineRow['decimalplaces']) . '" />';

//	echo '<input type="hidden" class="number" name="maxdispatchitemid" value="'.locale_number_format($maxdispatchitemid,$LineRow['decimalplaces']).'" />';

echo '</table>';
echo '<br /><div class="centre"><input type="submit" name="UpdateAll" value="' . _('Update') . '" /></div>
          </div>
          </form>';


include('includes/footer.inc');
