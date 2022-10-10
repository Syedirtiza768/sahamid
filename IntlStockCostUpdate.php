<?php

/* $Id: StockCostUpdate.php 6310 2013-08-29 10:42:50Z daintree $*/

$UpdateSecurity =10;

include('includes/session.inc');
$Title = _('International Stock Cost Update');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID =trim(mb_strtoupper($_POST['StockID']));
}

echo '<a href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a><br />';

echo '<p class="page_title_text">
     <img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Inventory Adjustment') . '" alt="" />
     ' . ' ' . $Title . '</p>';

if (isset($_POST['UpdateData'])){

	$sql = "SELECT materialcost,
					labourcost,
					overheadcost,
					mbflag,
					sum(quantity) as totalqoh
			FROM stockmaster INNER JOIN locstock 
			ON stockmaster.stockid=locstock.stockid
			WHERE stockmaster.stockid='".$StockID."'
			GROUP BY description,
					units,
					lastcost,
					actualcost,
					materialcost,
					labourcost,
					overheadcost,
					mbflag";
	$ErrMsg = _('The entered item code does not exist');
    $OldResult = DB_query($sql,$db,$ErrMsg);
    $OldRow = DB_fetch_array($OldResult);
	$sql2 = "SELECT price
					
					
			FROM pricefactor
			
			WHERE stockid='".$StockID."'
			";
	$ErrMsg2 = _('The entered item code does not exist');
    $OldResult2 = DB_query($sql2,$db,$ErrMsg2);
    $OldRow2 = DB_fetch_array($OldResult2);
    $_POST['QOH'] = $OldRow['totalqoh'];
    $_POST['OldMaterialCost'] = $OldRow['materialcost'];
    if ($OldRow['mbflag']=='M') {
        $_POST['OldLabourCost'] = $OldRow['labourcost'];
        $_POST['OldOverheadCost'] = $OldRow['overheadcost'];
    } else {
        $_POST['OldLabourCost'] = 0;
        $_POST['OldOverheadCost'] = 0;
        $_POST['LabourCost'] = 0;
        $_POST['OverheadCost'] = 0;
    }
    DB_free_result($OldResult);

 	$OldCost = $_POST['OldMaterialCost'] + $_POST['OldLabourCost'] + $_POST['OldOverheadCost'];
   	$NewCost = filter_number_format($_POST['MaterialCost']) + filter_number_format($_POST['LabourCost']) + filter_number_format($_POST['OverheadCost']);

	$OldIntlCost = $OldRow2['price'];
	$result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $StockID . "'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		prnMsg (_('The entered item code does not exist'),'error',_('Non-existent Item'));
	} elseif ($OldCost != $NewCost){

		$Result = DB_Txn_Begin($db);
		ItemCostUpdateGL($db, $StockID, $NewCost, $OldCost, $_POST['QOH']);

		$SQL = "UPDATE stockmaster SET	materialcost='" . filter_number_format($_POST['MaterialCost']) . "',
										labourcost='" . filter_number_format($_POST['LabourCost']) . "',
										overheadcost='" . filter_number_format($_POST['OverheadCost']) . "',
										lastcost='" . $OldCost . "',
										lastcostupdate ='" . Date('Y-m-d')."'
								WHERE stockid='" . $StockID . "'";

		$ErrMsg = _('The cost details for the stock item could not be updated because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$Result = DB_Txn_Commit($db);
		UpdateCost($db, $StockID); //Update any affected BOMs

	}
	$SQL = "select stockid from pricefactor where stockid = '" . $StockID . "'
	
	
	";
				$Result = DB_query($SQL, $db);
				echo mysqli_num_rows($Result);
			if (mysqli_num_rows($Result)>0)
				{
					$SQL = "UPDATE pricefactor
					SET price = '" .$_POST['IntlStockCost'] . "',
					currency = '" . $_POST['Abbreviation'] . "'
					WHERE stockid='" . $StockID . "'";
	$Result = DB_query($SQL, $db);
			
					
				}
				else
				{
					
					$SQL = "insert into pricefactor(stockid,price,currency) values
					('" .$StockID . "','" .$_POST['IntlStockCost']  ."','" .$_POST['Abbreviation'].   "'
					)";
	$Result = DB_query($SQL, $db);
					
				}

	
	
	
}

$ErrMsg = _('The cost details for the stock item could not be retrieved because');
$DbgMsg = _('The SQL that failed was');

$result = DB_query("SELECT description,
							units,
							lastcost,
							actualcost,
							materialcost,
							labourcost,
							overheadcost,
							mbflag,
							stocktype,
							lastcostupdate,
							sum(quantity) as totalqoh
						FROM stockmaster INNER JOIN locstock
							ON stockmaster.stockid=locstock.stockid
							INNER JOIN stockcategory
							ON stockmaster.categoryid = stockcategory.categoryid
						WHERE stockmaster.stockid='" . $StockID . "'
						GROUP BY description,
							units,
							lastcost,
							actualcost,
							materialcost,
							labourcost,
							overheadcost,
							mbflag,
							stocktype",
						$db,$ErrMsg,$DbgMsg);


$myrow = DB_fetch_array($result);
$result2 = DB_query("SELECT 	stockid
							,price,
							lastupdate
						FROM pricefactor
						WHERE stockid='" . $StockID . "'
						",
						$db,$ErrMsg,$DbgMsg);


$myrow2 = DB_fetch_array($result2);


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table cellpadding="2" class="selection">';
echo '<tr><th colspan="2">' . _('Item Code') . ':<input type="text" name="StockID" value="' . $StockID . '"  maxlength="20" />';
echo '<input type="submit" name="Show" value="' . _('Show Cost Details') . '" /></th></tr>';
echo '<tr><th colspan="2">' . $StockID . ' - ' . $myrow['description'] . '</th></tr>';
echo '<tr><th colspan="2">' .  _('Total Quantity On Hand') . ': ' . $myrow['totalqoh'] . ' ' . $myrow['units']  . '</th></tr>';
echo '<tr><th colspan="2">' .  _('Last Cost update on') . ': ' . ConvertSQLDate($myrow2['lastupdate'])  . '</th></tr>';

if (($myrow['mbflag']=='D' AND $myrow['stocktype'] != 'L')
							OR $myrow['mbflag']=='A'
							OR $myrow['mbflag']=='K'){
    echo '</div>
          </form>'; // Close the form
   if ($myrow['mbflag']=='D'){
        echo '<br />' . $StockID .' ' . _('is a service item');
   } else if ($myrow['mbflag']=='A'){
        echo '<br />' . $StockID  .' '  . _('is an assembly part');
   } else if ($myrow['mbflag']=='K'){
        echo '<br />' . $StockID . ' ' . _('is a kit set part');
   }
   prnMsg(_('Cost information cannot be modified for kits assemblies or service items') . '. ' . _('Please select a different part'),'warn');
   include('includes/footer.inc');
   exit;
}

/*echo '<tr><td>';
echo '<input type="hidden" name="OldMaterialCost" value="' . $myrow['materialcost'] .'" />';
echo '<input type="hidden" name="OldLabourCost" value="' . $myrow['labourcost'] .'" />';
echo '<input type="hidden" name="OldOverheadCost" value="' . $myrow['overheadcost'] .'" />';
echo '<input type="hidden" name="QOH" value="' . $myrow['totalqoh'] .'" />';

echo _('Last Cost') .':</td>
		<td class="number">' . locale_number_format($myrow['lastcost'],$_SESSION['StandardCostDecimalPlaces']) . '</td></tr>';*/
if (! in_array($UpdateSecurity,$_SESSION['AllowedPageSecurityTokens'])){
	echo '<tr><td>' . _('Cost') . ':</td>
			<td class="number">' . locale_number_format($myrow['materialcost']+$myrow['labourcost']+$myrow['overheadcost'],$_SESSION['StandardCostDecimalPlaces']) . '</td>
		</tr>
		</table>';
} else {

	if ($myrow['mbflag']=='M'){
		echo '<tr><td><input type="hidden" name="MaterialCost" value="' . $myrow['materialcost'] . '" />';
		echo _('Standard Material Cost Per Unit') .':</td>
				<td class="number">' . locale_number_format($myrow['materialcost'],$_SESSION['StandardCostDecimalPlaces']) . '</td>
			</tr>';
		echo '<tr>
				<td>' . _('Standard Labour Cost Per Unit') . ':</td>
				<td class="number"><input type="text" class="number" name="LabourCost" value="' . locale_number_format($myrow['labourcost'],$_SESSION['StandardCostDecimalPlaces']) . '" /></td>
			</tr>';
		echo '<tr>
				<td>' . _('Standard Overhead Cost Per Unit') . ':</td>
				<td class="number"><input type="text" class="number" name="OverheadCost" value="' . locale_number_format($myrow['overheadcost'],$_SESSION['StandardCostDecimalPlaces']) . '" /></td>
			</tr>';
$sql = "SELECT		currency,
					currabrev,
					country,
					hundredsname,
					rate,
					decimalplaces,
					webcart
				FROM currencies";
	$result = DB_query($sql, $db);
			echo '
			<tr>
				<td>' ._('Currency') . ':</td>
				<td><select name="Abbreviation">';
		while($myrow = DB_fetch_array($result)) {
			echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['currabrev'] . ' - ' . $myrow['currency'] . '</option>';
		}

		echo '</select></td>
			</tr>';
			
			
	} elseif ($myrow['mbflag']=='B' OR  $myrow['mbflag']=='D') {
		echo '<tr>
				<td>' . _('Standard List Price') .':</td>
				<td class="number"><input type="text" class="number" name="MaterialCost" value="' . locale_number_format($myrow['materialcost'],$_SESSION['StandardCostDecimalPlaces']) . '" /></td>
			</tr>';
		echo '<tr>
				<td>' . _('International List Price') .':</td>
				<td class="number"><input type="text" class="number" name="IntlStockCost" value="' . $myrow2['price'] . '" /></td>
			</tr>';
		
		$sql = "SELECT	
						currency,
						currabrev,
						country,
						hundredsname,
						decimalplaces,
						rate,
						webcart
				FROM currencies
			";

		$ErrMsg = _('An error occurred in retrieving the currency information');;
		$result = DB_query($sql, $db, $ErrMsg);

		
			echo '
			<tr>
				<td>' ._('Currency') . ':</td>
				<td><select name="Abbreviation">';
		while($myrow = DB_fetch_array($result)) {
			echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['currabrev'] . ' - ' . $myrow['currency'] . '</option>';
		}

		echo '</select></td>
			</tr>';
	} else 	{
		echo '<tr><td><input type="hidden" name="LabourCost" value="0" />';
		echo '<input type="hidden" name="OverheadCost" value="0" /></td></tr>';
	}
    echo '</table>
         <br />
             <div class="centre">
                  <input type="submit" name="UpdateData" value="' . _('Update') . '" />
             </div>
         <br />
         <br />';
}
if ($myrow['mbflag']!='D'){
	echo '<div class="centre"><a href="' . $RootPath . '/StockStatus.php?StockID=' . $StockID . '">' . _('Show Stock Status') . '</a>';
	echo '<br /><a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '">' . _('Show Stock Movements') . '</a>';
	echo '<br /><a href="' . $RootPath . '/StockUsage.php?StockID=' . $StockID . '">' . _('Show Stock Usage')   . '</a>';
	echo '<br /><a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Sales Orders') . '</a>';
	echo '<br /><a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a></div>';
}
echo '</div>
      </form>';
include('includes/footer.inc');
?>