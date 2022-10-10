


<?php
/* $Id: SelectOrderItems.php 6549 2014-01-24 20:32:31Z daintree $*/


include('includes/DefineCartClass.php');

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/

include('includes/session.inc');
include('includes/header.inc');
$identifier=$_SESSION['identifier'];
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
						include('includes/SelectOrderItems_IntoCart.inc');
						echo'<script>window.close()</script>';
					}

				} elseif ($myrow['mbflag']=='G'){
					prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
				} else { /*Its not a kit set item*/
					include('includes/SelectOrderItems_IntoCart.inc');
					echo'<script>window.close()</script>';
				}
			}
		 }
		 unset($NewItem);
	 } /* end of if quick entry */
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
							include('includes/SelectOrderItems_IntoCart.inc');
						//	echo"say something";
							//print_r($NewItemArray);
						//		echo'<script>var win = window.open("additemsuccess.html", "_self");
							//	win.close();</script>';
						}

					} else { /*Its not a kit set item*/
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = 0;
						include('includes/SelectOrderItems_IntoCart.inc');
					//	echo"say something";
						//print_r($NewItemArray);
					//		echo'<script>var win = window.open("additemsuccess.html", "_self");
						//		win.close();</script>';
					}
				} /* end of if its a new item */
			} /*end of if its a new item */
		}/* loop through NewItem array */
	} /* if the NewItem_array is set */

echo '<form name="form"  action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?line='.$_GET['line'].'
			&option='.$_GET['option'].'" id="SelectParts" method="post">';
   	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
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

/* Now show the stock item selection search stuff below */
$count=0;
$nm='addnewitem'.$count;

	 if (1)  
		
		 
		{
$sqlnewlineno="select MAX(internalitemno) as maxinternalitemno from salesorderdetails where
			orderno = ".$_SESSION['orderno'].""; 		
$resultsqlnewlineno = DB_query($sqlnewlineno ,$db);
$resultrowsqlnewlineno=DB_fetch_array($resultsqlnewlineno);
if ($resultrowsqlnewlineno['maxinternalitemno'] != null)
{	$internalitemno = $resultrowsqlnewlineno['maxinternalitemno'] + 1;}
else
{$internalitemno=0;}
	
		echo '<input type="hidden" name="PartSearch" value="' .  _('Yes Please') . '" />
	<input type="hidden" name="newlineno" value='.$internalitemno.'>	
<input type="hidden" name="lne" value='. $_GET['line'].'>
			<input type="hidden" name="opt" value='.$_GET['option'].'>';
			$sqlOI="select MAX(optionitemno) as maxoptionitemno from salesorderdetails where
			orderno = ".$_SESSION['orderno']." AND orderlineno = ".$_GET['line']. 
			
			" AND lineoptionno = ".$_GET['option'].
			
			""; 		
$resultOI = DB_query($sqlOI,$db);
$resultOIrow=DB_fetch_array($resultOI);
if ($resultOIrow['maxoptionitemno'] != null)
{	$optionitemno = $resultOIrow['maxoptionitemno'] + 1;}
else
{$optionitemno=0;}
			//$optionitemno = $resultOIrow['maxoptionitemno'] + 1;
		echo	'<input type="hidden" name="optitemno" value='.$optionitemno.'>';
	
		echo '<br /><div class="centre">' . $msg;
		$lne=$_GET['line'] + 1;
		$opt=$_GET['option'] + 1;
		echo '<h3>Line No.'.$lne .' Option No.'.$opt .'</h3>';
		$SQL="SELECT * FROM salesorderdetails where orderno='".$_SESSION['orderno']."' AND orderlineno='".$_GET['line'] ."'
		AND lineoptionno='".$_GET['option'] ."'";
		
		$result=DB_query($SQL,$db);
		echo"<h3>Items added so far</h3>
		<table><tr><td>Stock Code</td><td>quantity</td></tr>";
		while($row=DB_fetch_array($result))
		echo "<tr><td>".$row['stkcode']."</td><td>".$row['quantity']."</td></tr>";
	echo"</table>";
		
		;
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ';
		echo _('Search for  Items') . '</p></div>';
		echo '<div class="page_help_text">' . _('Search for Items') . _(', Searches the database for items, you can narrow the results by selecting a stock category, or just enter a partial item description or partial item code') . '.</div><br />';
		echo '<table class="selection">
				<tr>
					<td><b>' . _('Select a Stock Category') . ': </b><select tabindex="1" name="StockCat">';

		if (!isset($_POST['StockCat']) OR $_POST['StockCat']=='All'){
			echo '<option selected="selected" value="All">' . _('All') . '</option>';
			$_POST['StockCat'] = 'All';
		} else {
			echo '<option value="All">' . _('All') . '</option>';
		}
		$SQL="SELECT categoryid,
						categorydescription
				FROM stockcategory
				WHERE stocktype='F' OR stocktype='D' OR stocktype='L'
				ORDER BY categorydescription";

		$result1 = DB_query($SQL,$db);
		while ($myrow1 = DB_fetch_array($result1)) {
			if ($_POST['StockCat']==$myrow1['categoryid']){
				echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
			} else {
				echo '<option value="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
			}
		}

		echo '</select></td>
			<td><b>' . _('Enter partial Description') . ':</b><input tabindex="2" type="text" name="Keywords" size="20" maxlength="25" value="' ;

        if (isset($_POST['Keywords'])) {
             echo $_POST['Keywords'] ;
        }
        echo '" /></td>';

		echo '<td align="right"><b>' . _('OR') .  ' ' . _('Enter extract of the Stock Code or partno or manufacturer code') . ':</b><input tabindex="3" type="text" ' . (!isset($_POST['PartSearch']) ? 'autofocus="autofocus"' :'') . ' name="StockCode" size="15" maxlength="18" value="';
        if (isset($_POST['StockCode'])) {
            echo  $_POST['StockCode'];
        }
	echo '" /></td>

			</tr>';

		echo '<tr>
			<td style="text-align:center" colspan="7"><input tabindex="4" type="submit" name="Search" value="' . _('Search Now') . '" /></td>
			';

		if (in_array($_SESSION['PageSecurityArray']['ConfirmDispatch_Invoice.php'], $_SESSION['AllowedPageSecurityTokens'])){ //not a customer entry of own order
			echo '';
		}
        echo '</tr>
			</table>
			<br />
			</div>';
		if (isset($SearchResult)) {
			unset($_SESSION[$nm]);
			echo '<br />';
			echo '<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Order when ready.') . '</div>';
			echo '<br />';
			$j = 1;
			//echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier . '" method="post" name="orderform">';
            echo '<div>';
			echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
			echo '<table class="table1">';
			
			echo '<tr><td colspan="1"><input type="hidden" name="PreviousList" value="'.strval($Offset-1).'" />
			
		
		
			<input tabindex="'.strval($j+8).'" type="submit" name="Previous" value="'._('Previous').'" /></td>';
			echo '<td style="text-align:center" colspan="6"><input type="hidden" name="SelectingOrderItems" value="1" /><input tabindex="'.strval($j+9).'" type="submit" value="'._('Add to Option').'" /></td>';
			
			echo '<td colspan="1"><input type="hidden" name="NextList" value="'.strval($Offset+1).'" />
			
			<input tabindex="'.strval($j+10).'" type="submit" name="Next" value="'._('Next').'" /></td></tr>';
			echo '<tr>
					<th class="ascending" >' . _('Code') . '</th>
		   			<th class="ascending" >' . _('Description') . '</th>
		   			<th>' . _('Units') . '</th>
		   			<th class="ascending" >' . _('On Hand') . '</th>
		   			<th class="ascending" >' . _('On Demand') . '</th>
		   			<th class="ascending" >' . _('On Order') . '</th>
		   			<th class="ascending" >' . _('Available') . '</th>
		   			<th>' . _('Quantity') . '</th>
		   		</tr>';
			$ImageSource = _('No Image');
			$i=0;
			$k=0; //row colour counter

			while ($myrow=DB_fetch_array($SearchResult)) {

				// Find the quantity in stock at location
				$QOHSQL = "SELECT quantity AS qoh,
									stockmaster.decimalplaces
							   FROM locstock INNER JOIN stockmaster
							   ON locstock.stockid = stockmaster.stockid
							   WHERE locstock.stockid='" .$myrow['stockid'] . "'";
				$QOHResult =  DB_query($QOHSQL,$db);
				$QOHRow = DB_fetch_array($QOHResult);
				$QOH = $QOHRow['qoh'];

				// Find the quantity on outstanding sales orders
				$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
						FROM salesorderdetails INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
						 WHERE  salesorders.fromstkloc='" . $_SESSION['loccode'] . "'
						 AND salesorderdetails.completed=0
						 AND salesorders.quotation=0
						 AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";

				$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['loccode'] . ' ' . _('cannot be retrieved because');
				$DemandResult = DB_query($sql,$db,$ErrMsg);

				$DemandRow = DB_fetch_row($DemandResult);
				if ($DemandRow[0] != null){
					$DemandQty =  $DemandRow[0];
				} else {
					$DemandQty = 0;
				}

				// Find the quantity on purchase orders
				$sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo
						 FROM purchorderdetails INNER JOIN purchorders
						 ON purchorderdetails.orderno=purchorders.orderno
						 WHERE purchorderdetails.completed=0
						 AND purchorders.status<>'Cancelled'
						 AND purchorders.status<>'Rejected'
						 AND purchorders.status<>'Pending'
						 AND purchorders.status<>'Completed'
						AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

				$ErrMsg = _('The order details for this product cannot be retrieved because');
				$PurchResult = DB_query($sql,$db,$ErrMsg);

				$PurchRow = DB_fetch_row($PurchResult);
				if ($PurchRow[0]!=null){
					$PurchQty =  $PurchRow[0];
				} else {
					$PurchQty = 0;
				}

				// Find the quantity on works orders
				$sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
					   FROM woitems
					   WHERE stockid='" . $myrow['stockid'] ."'";
				$ErrMsg = _('The order details for this product cannot be retrieved because');
				$WoResult = DB_query($sql,$db,$ErrMsg);

				$WoRow = DB_fetch_row($WoResult);
				if ($WoRow[0]!=null){
					$WoQty =  $WoRow[0];
				} else {
					$WoQty = 0;
				}

				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				$OnOrder = $PurchQty + $WoQty;
				$Available = $QOH - $DemandQty + $OnOrder;

				printf('<td>%s</td>
						<td title="%s">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td><input class="number" tabindex="%s" type="text" size="6" name="OrderQty%s"  ' . ($i==0 ? 'autofocus="autofocus"':'') . ' value="0" min="0"/>
						<input type="hidden" name="StockID%s" value="%s" />
						</td>
						</tr>',
						$myrow['stockid'],
						$myrow['longdescription'],
						$myrow['description'],
						$myrow['units'],
						locale_number_format($QOH,$QOHRow['decimalplaces']),
						locale_number_format($DemandQty,$QOHRow['decimalplaces']),
						locale_number_format($OnOrder,$QOHRow['decimalplaces']),
						locale_number_format($Available,$QOHRow['decimalplaces']),
						strval($j+7),
						$i,
						$i,
						$myrow['stockid'] );
				$i++;
				$j++;
	#end of page full new headings if
			}
	#end of while loop
			echo '<tr>
					<td><input type="hidden" name="PreviousList" value="'. strval($Offset-1).'" /><input tabindex="'. strval($j+7).'" type="submit" name="Previous" value="'._('Previous').'" /></td>
					<td style="text-align:center" colspan="6"><input type="hidden" name="SelectingOrderItems" value="1" /><input tabindex="'. strval($j+8).'" type="submit" value="'._('Add to Option').'" /></td>
					<td><input type="hidden" name="NextList" value="'.strval($Offset+1).'" /><input tabindex="'.strval($j+9).'" type="submit" name="Next" value="'._('Next').'" /></td>
				</tr>
				</table>
				</div>
				';
			echo $jsCall;

		}#end if SearchResults to show
	} /*end of PartSearch options to be displayed */

echo '</form>';

$_SESSION['flag']="up";

include('includes/footer.inc');
?>
