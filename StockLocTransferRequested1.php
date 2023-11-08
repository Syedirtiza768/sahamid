<?php
/* $Id: StockLocTransfer.php 6312 2013-08-30 21:08:37Z daintree $*/

include('includes/session.inc');
$Title = _('Inventory Location Transfer Shipment');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['toloc'.$_POST['slct']]))
	$_SESSION['toloc'] = $_POST['toloc'.$_POST['slct']];
if(isset($_POST['fromloc'.$_POST['slct']]))
	$_SESSION['fromloc'] = $_POST['fromloc'.$_POST['slct']];
if(isset($_POST['authorizer'.$_POST['slct']]))
	$_SESSION['tosalesperson'] = $_POST['authorizer'.$_POST['slct']];
$RequestID=$_POST['slct'];
foreach ($_POST as $key => $value) {
	if (strpos($key, $RequestID) !== false) {
//		echo $_POST[$RequestID.'maxdispatchitemid'];
    for ($ind=0;$ind<=$_POST[$RequestID.'maxdispatchitemid'];$ind++){
				$LineID = $ind;
		$Quantity = filter_number_format($_POST[$RequestID.'Qty'.$LineID]);
		$StockID = $_POST[$RequestID.'StockID'.$LineID];
		$Location = $_POST[$RequestID.'Location'.$LineID];
		$Department = $_POST[$RequestID.'Department'.$LineID];
		$Tag = $_POST[$RequestID.'Tag'.$LineID];
echo		$RequestedQuantity = filter_number_format($_POST[$RequestID.'RequestedQuantity'.$LineID]);
		if($StockID!='')
		{
			$SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $StockID . "'
						AND loccode= '" . $_SESSION['fromloc'] . "'";
			$Result = DB_query($SQL, $db);
			if (DB_num_rows($Result)==1){
				$LocQtyRow = DB_fetch_row($Result);
				$QtyOnHandPrior = $LocQtyRow[0];
			} else {
				// There must actually be some error this should never happen
				$QtyOnHandPrior = 0;
			}
			round($QtyOnHandPrior - $Quantity,0);
				if	(round($QtyOnHandPrior - $Quantity,0) < 0){
			prnMsg (_('Negative quantity Not Allowed'),'error');
			include('includes/footer.inc');
			exit;

			}
			}    
    }
	}
	
	}
//print_r($_SESSION);
/*
	//		$RequestID = mb_substr($key,0, mb_strpos($key,'Qty'));
			$RequestID = $_POST['slct'];
	//		if($_POST[''])
			$LineID = mb_substr($key,mb_strpos($key,'Qty')+3);
			$Quantity = filter_number_format($_POST[$RequestID.'Qty'.$LineID]);
			$StockID = $_POST[$RequestID.'StockID'.$LineID];
			$Location = $_POST[$RequestID.'Location'.$LineID];
			$Department = $_POST[$RequestID.'Department'.$LineID];
			$Tag = $_POST[$RequestID.'Tag'.$LineID];
			$RequestedQuantity = filter_number_format($_POST[$RequestID.'RequestedQuantity'.$LineID]);
			$SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $StockID . "'
						AND loccode= '" . $_SESSION['fromloc'] . "'";
			$Result = DB_query($SQL, $db);
			if (DB_num_rows($Result)==1){
				$LocQtyRow = DB_fetch_row($Result);
				$QtyOnHandPrior = $LocQtyRow[0];
			} else {
				// There must actually be some error this should never happen
				$QtyOnHandPrior = 0;
			}
			echo round($QtyOnHandPrior - $LineItems->Quantity,0);
				if	(round($QtyOnHandPrior - $LineItems->Quantity,0) < 0){
			prnMsg (_('Negative quantity Not Allowed'),'error');
			include('includes/footer.inc');
			exit;

			}

			
	}
}
*/
if (isset($_POST['UpdateAll'])) {
	
	foreach ($_POST as $key => $value) {
		if (mb_strpos($key,'Qty')) {
			$RequestID = mb_substr($key,0, mb_strpos($key,'Qty'));
			$LineID = mb_substr($key,mb_strpos($key,'Qty')+3);
			$Quantity = filter_number_format($_POST[$RequestID.'Qty'.$LineID]);
			$StockID = $_POST[$RequestID.'StockID'.$LineID];
			$Location = $_POST[$RequestID.'Location'.$LineID];
			$Department = $_POST[$RequestID.'Department'.$LineID];
			$Tag = $_POST[$RequestID.'Tag'.$LineID];
			$RequestedQuantity = filter_number_format($_POST[$RequestID.'RequestedQuantity'.$LineID]);
			if (isset($_POST[$RequestID.'Completed'.$LineID])) {
				$Completed=True;
			} else {
				$Completed=False;
			}

			$sql="SELECT materialcost, labourcost, overheadcost, decimalplaces FROM stockmaster WHERE stockid='".$StockID."'";
			$result=DB_query($sql, $db);
			$myrow=DB_fetch_array($result);
			$StandardCost=$myrow['materialcost']+$myrow['labourcost']+$myrow['overheadcost'];
			$DecimalPlaces = $myrow['decimalplaces'];

			$Narrative = _('Issue') . ' ' . $Quantity . ' ' . _('of') . ' '. $StockID . ' ' . _('to department') . ' ' . $Department . ' ' . _('from') . ' ' . $Location ;

			$AdjustmentNumber = GetNextTransNo(17,$db);
			$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
			$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

			$Result = DB_Txn_Begin($db);

			// Need to get the current location quantity will need it later for the stock movement
	echo		$SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $StockID . "'
						AND loccode= '" . $_SESSION['UserStockLocation']  . "'";
			$Result = DB_query($SQL, $db);
			if (DB_num_rows($Result)==1){
				$LocQtyRow = DB_fetch_row($Result);
				$QtyOnHandPrior = $LocQtyRow[0];
			} else {
				// There must actually be some error this should never happen
				$QtyOnHandPrior = 0;
			}

			if ($_SESSION['ProhibitNegativeStock']==0 OR ($_SESSION['ProhibitNegativeStock']==1 AND $QtyOnHandPrior >= $Quantity)) {
				$SQL = "SELECT locstock.quantity FROM locstock
						WHERE locstock.stockid='". $StockID ."' 
						AND loccode='".$_SESSION['UserStockLocation']."'";
				$ResultQ = DB_query($SQL, $db);
				$QtyOnHandPrior = 0;
				if (DB_num_rows($ResultQ)==1){
					$LocQtyRow = DB_fetch_row($ResultQ);
					$QtyOnHandPrior = $LocQtyRow[0];
				}
				$SQL = "INSERT INTO stockmoves (
									stockid,
									type,
									transno,
									loccode,
									trandate,
									prd,
									dispatchid,
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

/*
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
				$DbgMsg =  _('The following SQL to insert the stock movement record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
*/
	for ($i=0;$i < count($LineRow);$i++){

		if($_POST['StockID' . $i] != ''){
			$DecimalsSql = "SELECT decimalplaces
							FROM stockmaster
							WHERE stockid='" . $_POST['StockID' . $i] . "'";
			$DecimalResult = DB_Query($DecimalsSql, $db);
			$DecimalRow = DB_fetch_array($DecimalResult);
/*		echo	$sql = "INSERT INTO stockrequestitems (dispatchid,
								stockid,
								quantity,
								shipdate)
						VALUES ('" . $_POST['Trf_ID'] . "',
							'" . $_POST['StockID' . $i] . "',
							'" . round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) . "',
							'" . Date('Y-m-d H-i-s') . "')";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Location Transfer record for'). ' '.$_POST['StockID' . $i];
			$resultLocShip = DB_query($sql,$db, $ErrMsg);
*/			/*$SQL = "UPDATE locstock
					SET quantity = quantity - '" . round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) ."'
					WHERE stockid='" . $_POST['StockID' . $i] ."'
					AND loccode='" . $_POST['fromloc'.$_POST['slct']]  ."'";
					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				*/
		}
	}
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to COMMIT Location Transfer transaction');
	DB_Txn_Commit($db);

	prnMsg( _('The inventory transfer records have been created successfully'),'success');
	


				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
				$SQL = "UPDATE stockrequest
							SET storemanager = '".$_SESSION['UsersRealName']."'
								
							";
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);

				$SQL="UPDATE stockrequestitems
						
						SET qtydelivered=qtydelivered+" . $Quantity . ",
						shipdate = '".date("Y-m-d H:i:s")."'
						WHERE dispatchid=" . $RequestID . "
							AND dispatchitemsid=" . $LineID ;

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);
				
				
			/*	$SQL = "UPDATE locstock SET quantity = quantity - '" . $Quantity . "'
									WHERE stockid='" . $StockID . "'
										AND loccode='" . $Location . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');

				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
*/
				if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $StandardCost > 0){

					$StockGLCodes = GetStockGLCode($StockID,$db);

					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												amount,
												narrative,
												tag)
											VALUES (17,
												'"  .$AdjustmentNumber . "',
												'" . $SQLAdjustmentDate . "',
												'" . $PeriodNo . "',
												'" . $StockGLCodes['issueglact'] . "',
												'" . $StandardCost * ($Quantity) . "',
												'" . $Narrative . "',
												'" . $Tag . "'
											)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
					$DbgMsg = _('The following SQL to insert the GL entries was used');
					$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

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
					$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);
				}

				if (($Quantity >= $RequestedQuantity) OR $Completed==True) {
					$SQL="UPDATE stockrequestitems
								SET completed=1
								
							WHERE dispatchid='".$RequestID."'
								AND dispatchitemsid='".$LineID."'";
					$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);
				}

				$Result = DB_Txn_Commit($db);

				$ConfirmationText = _('An internal stock request for'). ' ' . $StockID . ' ' . _('has been fulfilled from location').' ' . $Location .' '. _('for a quantity of') . ' ' . locale_number_format($Quantity, $DecimalPlaces ) ;
				prnMsg( $ConfirmationText,'success');

				if ($_SESSION['InventoryManagerEmail']!=''){
					$ConfirmationText = $ConfirmationText . ' ' . _('by user') . ' ' . $_SESSION['UserID'] . ' ' . _('at') . ' ' . Date('Y-m-d H:i:s');
					$EmailSubject = _('Internal Stock Request Fulfillment for'). ' ' . $StockID;
					if($_SESSION['SmtpSetting']==0){
						      mail($_SESSION['InventoryManagerEmail'],$EmailSubject,$ConfirmationText);
					}else{
						include('includes/htmlMimeMail.php');
						$mail = new htmlMimeMail();
						$mail->setSubject($EmailSubject);
						$mail->setText($ConfirmationText);
						$result = SendmailBySmtp($mail,array($_SESSION['InventoryManagerEmail']));
					}

				
				}
			} else {
				$ConfirmationText = _('An internal stock request for'). ' ' . $StockID . ' ' . _('has been fulfilled from location').' ' . $Location .' '. _('for a quantity of') . ' ' . locale_number_format($Quantity, $DecimalPlaces) . ' ' . _('cannot be created as there is insufficient stock and your system is configured to not allow negative stocks');
				prnMsg( $ConfirmationText,'warn');
			}

			// Check if request can be closed and close if done.
			if (isset($RequestID)) {
				$SQL="SELECT dispatchid
						FROM stockrequestitems
						WHERE dispatchid='".$RequestID."'
							AND completed=0";
				$Result=DB_query($SQL, $db);
				if (DB_num_rows($Result)==0) {
					$SQL="UPDATE stockrequest
						SET closed=1
					WHERE dispatchid='".$RequestID."'";
					$Result=DB_query($SQL, $db);
				}
			}
		}
	}
}

if (isset($_POST['Submit']) OR isset($_POST['EnterMoreItems'])){
	  if ($_FILES['SelectedTransferFile']['name']) { //start file processing
	  	//initialize
	   	$InputError = false;
		$ErrorMessage='';
		//get file handle
		$FileHandle = fopen($_FILES['SelectedTransferFile']['tmp_name'], 'r');
		$TotalItems=0;
		//loop through file rows
		while ( ($myrow = fgetcsv($FileHandle, 10000, ',')) !== FALSE ) {

			if (count($myrow) != 2){
				prnMsg (_('File contains') . ' '. count($myrow) . ' ' . _('columns, but only 2 columns are expected. The comma separated file should have just two columns the first for the item code and the second for the quantity to transfer'),'error');
				fclose($FileHandle);
				include('includes/footer.inc');
				exit;
			}

			// cleanup the data (csv files often import with empty strings and such)
			$StockID='';
			$Quantity=0;
			for ($i=0; $i<count($myrow);$i++) {
				switch ($i) {
					case 0:
						$StockID = trim(mb_strtoupper($myrow[$i]));
						$result = DB_query("SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $StockID . "'",$db);
						$StockIDCheck = DB_fetch_row($result);
						if ($StockIDCheck[0]==0){
							$InputError = True;
							$ErrorMessage .= _('The part code entered of'). ' ' . $StockID . ' '. _('is not set up in the database') . '. ' . _('Only valid parts can be entered for transfers'). '<br />';
						}
						break;
					case 1:
						$Quantity = filter_number_format($myrow[$i]);
						if (!is_numeric($Quantity)){
						   $InputError = True;
						   $ErrorMessage .= _('The quantity entered for'). ' ' . $StockID . ' ' . _('of') . $Quantity . ' '. _('is not numeric.') . _('The quantity entered for transfers is expected to be numeric');
						}
						break;
				} // end switch statement
				if ($_SESSION['ProhibitNegativeStock']==1){
					$InTransitSQL="SELECT SUM(shipqty-recqty) as intransit
									FROM stockrequestitems
									WHERE stockid='" . $StockID . "'
										AND shiploc='".$_POST['fromloc'.$_POST['slct']]."'
										AND shipqty>recqty";
					$InTransitResult=DB_query($InTransitSQL, $db);
					$InTransitRow=DB_fetch_array($InTransitResult);
					$InTransitQuantity=$InTransitRow['intransit'];
					// Only if stock exists at this location
					$result = DB_query("SELECT quantity
										FROM locstock
										WHERE stockid='" . $StockID . "'
										AND loccode='".$_POST['fromloc'.$_POST['slct']]."'",
										$db);
					$CheckStockRow = DB_fetch_array($result);
					if (($CheckStockRow['quantity']-$InTransitQuantity) < $Quantity){
						$InputError = True;
						$ErrorMessage .= _('The item'). ' ' . $StockID . ' ' . _('does not have enough stock available (') . ' ' . $CheckStockRow['quantity'] . ')' . ' ' . _('The quantity required to transfer was') .  ' ' . $Quantity . '.<br />';
					}
				}
			} // end for loop through the columns on the row being processed
			if ($StockID!='' AND $Quantity!=0){
				$_POST['StockID' . $TotalItems] = $StockID;
				$_POST['StockQTY' . $TotalItems] = $Quantity;
				$StockID='';
				$Quantity=0;
				$TotalItems++;
			}
		  } //end while there are lines in the CSV file
		  $_POST['LinesCounter']=$TotalItems;
	   } //end if there is a CSV file to import
		  else { // process the manually input lines
			$ErrorMessage='';

			if (isset($_POST['ClearAll'])){
				unset($_POST['EnterMoreItems']);
				for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){
					unset($_POST['StockID' . $i]);
					unset($_POST['StockQTY' . $i]);
				}
			}
			$StockIDAccQty = array(); //set an array to hold all items' quantity
			for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){
				if (isset($_POST['Delete' . $i])){ //check box to delete the item is set
					unset($_POST['StockID' . $i]);
					unset($_POST['StockQTY' . $i]);
				}
				if (isset($_POST['StockID' . $i]) AND $_POST['StockID' . $i]!=''){
					$_POST['StockID' . $i]=trim(mb_strtoupper($_POST['StockID' . $i]));
					$result = DB_query("SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $_POST['StockID' . $i] . "'",$db);
					$myrow = DB_fetch_row($result);
					if ($myrow[0]==0){
						$InputError = True;
						$ErrorMessage .= _('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. _('is not set up in the database') . '. ' . _('Only valid parts can be entered for transfers'). '<br />';
						$_POST['LinesCounter'] -= 10;
					}
					DB_free_result( $result );
					if (!is_numeric(filter_number_format($_POST['StockQTY' . $i]))){
						$InputError = True;
						$ErrorMessage .= _('The quantity entered of'). ' ' . $_POST['StockQTY' . $i] . ' '. _('for part code'). ' ' . $_POST['StockID' . $i] . ' '. _('is not numeric') . '. ' . _('The quantity entered for transfers is expected to be numeric') . '<br />';
						$_POST['LinesCounter'] -= 10;
					}
					
					if ($_SESSION['ProhibitNegativeStock']==1){
						$InTransitSQL="SELECT SUM(shipqty-recqty) as intransit
										FROM stockrequestitems
										WHERE stockid='" . $_POST['StockID' . $i] . "'
											AND shiploc='".$_POST['fromloc'.$_POST['slct']]."'
											AND shipqty>recqty";
						$InTransitResult=DB_query($InTransitSQL, $db);
						$InTransitRow=DB_fetch_array($InTransitResult);
						$InTransitQuantity=$InTransitRow['intransit'];
						// Only if stock exists at this location
						$result = DB_query("SELECT quantity
											FROM locstock
											WHERE stockid='" . $_POST['StockID' . $i] . "'
											AND loccode='".$_POST['fromloc'.$_POST['slct']]."'",
											$db);

						$myrow = DB_fetch_array($result);
						if (($myrow['quantity']-$InTransitQuantity) < filter_number_format($_POST['StockQTY' . $i])){
							$InputError = True;
							$ErrorMessage .= _('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. _('does not have enough stock available for transfer.') . '.<br />';
							$_POST['LinesCounter'] -= 10;
						}
					}
					// Check the accumulated quantity for each item
					if(isset($StockIDAccQty[$_POST['StockID'.$i]])){
						$StockIDAccQty[$_POST['StockID'.$i]] += filter_number_format($_POST['StockQTY' . $i]);
						if($myrow[0] < $StockIDAccQty[$_POST['StockID'.$i]]){
							$InputError = True;
							$ErrorMessage .=_('The part code entered of'). ' ' . $_POST['StockID'.$i] . ' '._('does not have enough stock available for transter due to accumulated quantity is over quantity on hand.') . '<br />';
							$_POST['LinesCounter'] -= 10;
						}
					} else {
						$StockIDAccQty[$_POST['StockID'.$i]] = filter_number_format($_POST['StockQTY' . $i]);
					} //end of accumulated check

					DB_free_result( $result );
					$TotalItems++;
				}
			}//for all LinesCounter
		}

		if ($TotalItems == 0){
			$InputError = True;
			$ErrorMessage .= _('You must enter at least 1 Stock Item to transfer') . '<br />';
		}

	/*Ship location and Receive location are different 
		if ($_POST['fromloc'.$_POST['slct']]==$_POST['toloc'.$_POST['slct']]){
			$InputError=True;
			$ErrorMessage .= _('The transfer must have a different location to receive into and location sent from');
		}*/
	 } //end if the transfer is not a duplicated


if(isset($_POST['Submit']) AND $InputError==False){

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to BEGIN Location Transfer transaction');

	DB_Txn_Begin($db);
if ($_POST['toloc'.$_POST['slct']] == $_POST['fromloc'.$_POST['slct']]){
	$RequestNo = GetNextTransNo(38, $db);

		 $HeaderSQL="INSERT INTO posdispatch (dispatchid,
											loccode,
											despatchdate,
											
											deliveredto,
											storemanager
											)
										VALUES(
											'" . $RequestNo . "',
											
											'" . $_SESSION['toloc'] . "',
										
											'" .Date("Y-m-d"). "',
											'" . $_SESSION['tosalesperson'] . "',
											'" . $_SESSION['UsersRealName']. "')";
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);
}
	for ($i=0;$i < $_POST['LinesCounter'];$i++){

		if($_POST['StockID' . $i] != ''){
			$DecimalsSql = "SELECT decimalplaces
							FROM stockmaster
							WHERE stockid='" . $_POST['StockID' . $i] . "'";
			$DecimalResult = DB_Query($DecimalsSql, $db);
			$DecimalRow = DB_fetch_array($DecimalResult);
			if ($_POST['toloc'.$_POST['slct']] == $_POST['fromloc'.$_POST['slct']]){

			$sql = "INSERT INTO posdispatchitems (dispatchid,
								dispatchitemsid,
								stockid,
								quantity)
						VALUES (" . $RequestNo . ",
								" . $i . ",
							'" . $_POST['StockID' . $i] . "',
							" . round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) . ")";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Location Transfer record for'). ' '.$_POST['StockID' . $i];
			$resultLocShip = DB_query($sql,$db, $ErrMsg);
			
				$SQL = "select stockid, salesperson from stockissuance where stockid = '" . $_POST['StockID' . $i]. "'
	
	and salesperson = '" . $_SESSION['tosalesperson'] . "'
	
	";
				$Result = DB_query($SQL, $db);
				echo mysqli_num_rows($Result);
					echo					$SQL="SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $LineItems->StockID . "'
						AND loccode= '" . $_SESSION['UserStockLocation']  . "'";

			$ResultQ = DB_query($SQL, $db);
			if (DB_num_rows($ResultQ)==1){
					$LocQtyRow = DB_fetch_row($ResultQ);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}
		echo $qty = $_POST['StockQTY' . $i];
				$SQL = "SELECT locstock.quantity FROM locstock
						WHERE locstock.stockid='". $_POST['StockID' . $i] ."' 
						AND loccode='".$_SESSION['UserStockLocation']."'";
				$ResultQ = DB_query($SQL, $db);
				$QtyOnHandPrior = 0;
				if (DB_num_rows($ResultQ)==1){
					$LocQtyRow = DB_fetch_row($ResultQ);
					$QtyOnHandPrior = $LocQtyRow[0];
				}

			echo	$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												reference,
												qty,
												newqoh
												)
					
					VALUES (
						'" . $_POST['StockID' . $i] . "',
						513,
						'" . $RequestNo . "',
						'" . $_SESSION['UserStockLocation'] . "',
							'" . Date("Y-m-d") . "',
						'" . _('To') . ' ' . $_SESSION['tosalesperson'] ."'
						,'" . round($qty,0). "'
						,'" .round($QtyOnHandPrior - $qty,0). "'
						)";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the incoming stock cannot be added because');
				$DbgMsg =  _('The following SQL to insert the stock movement record was used');
				$ResultQ = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


			
				
				
				
				
			if (mysqli_num_rows($Result)>0)
				{
					$SQL = "UPDATE stockissuance
					SET issued = issued + '" . $_POST['StockQTY' . $i] . "'
					WHERE stockid='" . $_POST['StockID' . $i]. "'
					AND salesperson='" . $_SESSION['tosalesperson'] . "'";
	$Result = DB_query($SQL, $db);
			
					
				}
				else
				{
					
					$SQL = "insert into stockissuance(salesperson,stockid,issued) values
					('" . $_SESSION['tosalesperson'] . "','" . $_POST['StockID' . $i]. "'
					,'" . $_POST['StockQTY' . $i] . "')";
	$Result = DB_query($SQL, $db);
					
	
				}
	
$SQL = "UPDATE locstock
					SET quantity = quantity - '" . $_POST['StockQTY' . $i] . "'
					WHERE stockid='" . $_POST['StockID' . $i]. "'
					AND loccode='" . $_SESSION['toloc'] . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	$SQL = "SELECT defaultsubstore from locations where loccode = '" . $_SESSION['toloc']. "'";	
$Result = DB_query($SQL, $db);		
$myrowA=DB_fetch_array($Result);


$SQL = "UPDATE substorestock
					SET quantity = quantity - '" . $_POST['StockQTY' . $i] . "'
					WHERE stockid='" . $_POST['StockID' . $i]. "'
					AND substoreid=" .  $myrowA['defaultsubstore'] . "";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
}
			
			}
		
			/*$SQL = "UPDATE locstock
					SET quantity = quantity - '" . round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) ."'
					WHERE stockid='" . $_POST['StockID' . $i] ."'
					AND loccode='" . $_POST['fromloc'.$_POST['slct']]  ."'";
					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		*/
		}
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to COMMIT Location Transfer transaction');
	DB_Txn_Commit($db);

	prnMsg( _('The inventory transfer records have been created successfully'),'success');
	
	if ($_SESSION['toloc'] == $_SESSION['fromloc'])
	echo '<p><a href="'.$RootPath.'/PDFOGP.php?RequestNo=' . $RequestNo . '">' .  _('Print the OGP'). '</a></p>';
			else	
	echo '<p><a href="'.$RootPath.'/PDFStockLocTransfer.php?TransferNo=' . $_POST['Trf_ID'] . '">' .  _('Print the Transfer Docket'). '</a></p>';
	include('includes/footer.inc');
	}
	

 else {
	//Get next Inventory Transfer Shipment dispatchid Number
	if (isset($_GET['Trf_ID'])){
		$Trf_ID = $_GET['Trf_ID'];
	} elseif (isset($_POST['Trf_ID'])){
		$Trf_ID = $_POST['Trf_ID'];
	}

	if(!isset($Trf_ID)){
		$Trf_ID = $RequestID;
	}

	if (isset($InputError) and $InputError==true){
		echo '<br />';

		prnMsg($ErrorMessage, 'error');
		echo '<br />';

	}

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	$x = $_POST['Location'];
//	echo "hi";
	$c = $_POST['slct'];
//echo "$c";
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="4"><input type="hidden" name="Trf_ID" value="' . $Trf_ID . '" /><h3>' .  _('Inventory Location Transfer Shipment dispatchid').' # '. $Trf_ID. '</h3></th>
		</tr>';

	$sql = "SELECT loccode, locationname FROM locations ORDER BY locationname";
	$resultStkLocs = DB_query($sql,$db);

	echo '<tr>
			<td>' . _('From Stock Location') . ':</td>
			<td><input type = "text" readonly = "readonly" value = '."'".$_POST['fromloc'.$_POST['slct']]."'".' name="FromStockLocation"></td>';
	
	;

	DB_data_seek($resultStkLocs,0); //go back to the start of the locations result
	echo '<td>' . _('To Stock Location').':</td>
	<td><input type = "text" readonly = "readonly" value = '."'".$_POST['toloc'.$_POST['slct']]."'".' name="ToStockLocation"></td>';
	
	;

	echo '<tr style = "position:absolute; left:-999px;">
			<td>' . _('Upload CSV file of Transfer Items and Quantites') . ':</td>
			<td><input name="SelectedTransferFile" type="file" /></td>
		  </tr>
		  </table>
		  <br />
		  <table class="selection">
			<tr>
				<th>' .  _('Item Code'). '</th>
				<th>' .  _('Quantity'). '</th>
				<th style = "position:absolute; left:-999px;">' . _('Clear All') . ':<input type="checkbox" name="ClearAll" /></th>
			</tr>';

	$j=0; /* row counter for reindexing */
	if(isset($_POST['LinesCounter'])){

		for ($i=0;$i < $_POST['LinesCounter'];$i++){
			if (!isset($_POST['StockID'. $i]) or $_POST['StockQTY'. $i] !== 0){
				continue;
			}
			if ($_POST['StockID' . $i] ==''){
				break;
			}

			echo '<tr>
					<td><input type="text" readonly = "readonly" name="StockID' . $i .'" size="21"  maxlength="20" value="' . $_POST['StockID' . $i] . '" /></td>
					<td><input type="text" readonly = "readonly" name="StockQTY' . $i .'" size="10" maxlength="10" class="number" value="' . locale_number_format($_POST['StockQTY' . $i],'Variable') . '" /></td>
					</tr>';
			$j++;
		}
	} else {
		$j = 0;
	}
	// $i is incremented an extra time, so 9 to get 10...
//	$z=($j + 9);

$tempid = $_POST['slct'].'maxdispatchitemid';

$num = $_POST[$tempid];


	while($j <= $num) {
		
		$temp1 = $_POST['slct'];
			$temp1 .= "StockID";
			$temp1 .= $j;
		//	echo "<br>$temp1";
			$x = $_POST[$temp1];
			$temp2 = $_POST['slct'];
			$temp2 .= "Qty";
			$temp2 .= $j;
			//echo "<br>$temp1";
			$x = $_POST[$temp1];
			$y = $_POST[$temp2];
	//		echo "<br>--- $x -----";
echo $y;
if ($y > 0)
{
		echo '<tr>
					<td><input type="text" readonly = "readonly" name="StockID' . $j .'" size="21"  maxlength="20" value='.$x.' /></td>
					<td><input type="number" readonly = "readonly" name="StockQTY' . $j .'" size="10" maxlength="10" class="number"value='.$y.'  /></td>
					</tr>';
}	
	$j++;
		
	}

	echo '</table>
		<br />
		<div class="centre">
		<input type="hidden" name="LinesCounter" value="'. $j .'" />
		<input type="hidden" name="EnterMoreItems" value="'. _('Add More Items'). '" />
		<input type="submit" name="Submit" value="'. _('Create Transfer Shipment'). '" />
		<br />
		</div>
		</div>
		</form>';
	include('includes/footer.inc');
}
?>
