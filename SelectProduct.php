<?php


/* $Id: SelectProduct.php 6519 2013-12-26 18:45:22Z rchacon $*/

$PricesSecurity = 12;//don't show pricing info unless security token 12 available to user
$SuppliersSecurity = 9; //don't show supplier purchasing info unless security token 9 available to user

include ('includes/session.inc');
$Title = _('Search Inventory Items');
/* webERP manual links before header.inc */
$ViewTopic= 'Inventory';
$BookMark = 'SelectingInventory';

include ('includes/header.inc');

error_reporting(0);
if (isset($_GET['brand']))
$_SESSION['brand'] = $_GET['brand'];
//else
//$_SESSION['brand'] = 'All';
if (isset($_GET['StockID'])) {
	//The page is called with a StockID
	$_GET['StockID'] = trim(mb_strtoupper($_GET['StockID']));
	$_GET['Select'] = trim(mb_strtoupper($_GET['StockID']));
}
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Inventory Items') . '" alt="" />' . ' ' . _('Inventory Items') . '</p>';
if (isset($_GET['NewSearch']) or isset($_GET['Next']) or isset($_GET['Previous']) or isset($_GET['Go'])) {
	unset($StockID);
	unset($_SESSION['SelectedStockItem']);
	unset($_GET['Select']);
}
if (!isset($_GET['PageOffset'])) {
	$_GET['PageOffset'] = 1;
} else {
	if ($_GET['PageOffset'] == 0) {
		$_GET['PageOffset'] = 1;
	}
}
if (isset($_GET['StockCode'])) {
	$_GET['StockCode'] = trim(mb_strtoupper($_GET['StockCode']));
}
// Always show the search facilities
//error_reporting(0);

$SQL = "SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$result1 = DB_query($SQL, $db);
if(isset($_GET['UpdateCategories']))
{

if ($_GET['StockCat'] == 'All')
{
	$SQL = "SELECT * from manufacturers";
}
else
{
$SQL = "SELECT distinct manufacturers_id,
				manufacturers_name
		FROM manufacturers, stockmaster, stockcategory
		where stockmaster.brand = manufacturers.manufacturers_id
		and stockmaster.categoryid = stockcategory.categoryid
		and stockcategory.categoryid = "."'".$_GET['StockCat']."'"."
		ORDER BY manufacturers_name";
}
		$result2 = DB_query($SQL, $db);

}

else
{
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

// end of showing search facilities
/* displays item options if there is one and only one selected */
if (!isset($_GET['Search']) AND (isset($_GET['Select']) OR isset($_SESSION['SelectedStockItem']))) {
	if (isset($_GET['Select'])) {
		$_SESSION['SelectedStockItem'] = $_GET['Select'];
		$StockID = $_GET['Select'];
		unset($_GET['Select']);
	} else {
		$StockID = $_SESSION['SelectedStockItem'];
	}

	$result = DB_query("SELECT stockmaster.description,
								stockmaster.longdescription,
								stockmaster.mbflag,
								stockcategory.stocktype,
								stockmaster.units,
								stockmaster.decimalplaces,
								stockmaster.mnfCode,
								stockmaster.mnfpno,
								stockmaster.conditionID,
								stockmaster.conditionID,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS cost,
								stockmaster.discontinued,
								stockmaster.eoq,
								stockmaster.volume,
								stockmaster.grossweight,
								stockcategory.categorydescription,
								stockmaster.categoryid
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE stockid='" . $StockID . "'", $db);
	$myrow = DB_fetch_array($result);
	$Its_A_Kitset_Assembly_Or_Dummy = false;
	$Its_A_Dummy = false;
	$Its_A_Kitset = false;
	$Its_A_Labour_Item = false;
	if ($myrow['discontinued']==1){
		$ItemStatus = '<p class="bad">' ._('Obsolete') . '</p>';
	} else {
		$ItemStatus = '';
	}
	echo '<table width="90%">
			<tr>
				<th colspan="3"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b title="' . $myrow['longdescription'] . '">' . ' ' . $StockID . ' - ' . $myrow['description'] . '</b> ' . $ItemStatus . '</th>
			</tr>';


	echo '<tr>
			<td style="width:40%" valign="top">
			<table>'; //nested table
	echo '<tr><th class="number">' . _('Category') . ':</th> <td colspan="2" class="select">' . $myrow['categorydescription'] , '</td></tr>';
	echo '<tr><th class="number">' . _('Manufacturers code') . ':</th> <td colspan="2" class="select">' . $myrow['mnfCode'] , '</td></tr>';
	echo '<tr><th class="number">' . _('Part Number') . ':</th> <td colspan="2" class="select">' . $myrow['mnfpno'] , '</td></tr>';
	
	echo '</td><th style = "position:fixed; left:-999px;" class="number">' . _('Control Level') . ':</th><td style = "position:fixed; left:-999px;" class="select">';
	if ($myrow['serialised'] == 1) {
		echo _('serialised');
	} elseif ($myrow['controlled'] == 1) {
		echo _('Batchs/Lots');
	} else {
		echo _('N/A');
	}
	echo '</td><th style = "position:fixed; left:-999px;" class="number">' . _('Units') . ':</th>
			<td style = "position:fixed; left:-999px;" class="select">' . $myrow['units'] . '</td></tr>';
	echo '<tr style = "position:fixed; left:-999px;"><th class="number">' . _('Volume') . ':</th>
			<td class="select" colspan="2">' . locale_number_format($myrow['volume'], 3) . '</td>
			<th class="number">' . _('Weight') . ':</th>
			<td class="select">' . locale_number_format($myrow['grossweight'], 3) . '</td>
			<th class="number">' . _('EOQ') . ':</th>
			<td class="select">' . locale_number_format($myrow['eoq'], $myrow['decimalplaces']) . '</td></tr>';

	if(userHasPermission($db,"display_list_price")) {

	$sql = "select materialcost, lastcostupdate,minimumqty,minimumqtyupdatedat,minimumqtyupdatedby from stockmaster where stockid = '".$StockID."'";
		$listpriceresult = DB_query($sql,$db);
		$listprice = DB_fetch_array($listpriceresult);
		$sql2 = "select * from pricefactor where stockid like '%".$StockID."%'";
		$intlpriceresult = DB_query($sql2,$db);
		$intlprice = DB_fetch_array($intlpriceresult);
		
		$sql3 = "select rate from currencies where currabrev = '".$intlprice['currency']."'";
		$currencyrateresult = DB_query($sql3,$db);
		$currencyrate = DB_fetch_array($currencyrateresult);



			echo '<tr>
				<th class="number">' . _('Minimum Qty') . '</th>
				<td class="select">' . $listprice['minimumqty'] . '</td>';

				echo'<th class="number">' . _('Minimum Qty Updated By') . '</th>
				<td class="select">' . $listprice['minimumqtyupdatedby']." at " .$listprice['minimumqtyupdatedat']. '</td>';
			echo'</tr>';


			if(userHasPermission($db,"display_list_price")){
		echo '<tr>
				<th class="number">' . _('Last International Price Update') . '</th>
				<td class="select">' . $intlprice['lastupdate'] . '</td>';
		if(userHasPermission($db,"last_list_price_update")){
		echo'<th class="number">' . _('Last List Price update') . '</th>
				<td class="select">' . $listprice['lastcostupdate'] . '</td>';}
		echo'</tr>';


		echo '<tr>
				<th class="number">' . _('List Price') . '</th>
				<td class="select">' . locale_number_format($listprice['materialcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			</tr>';

			echo'</tr>';
			}
			
		if ($intlprice['showbyair'] == 'yes' || $_SESSION['AccessLevel'] == 10)
		{
		echo '<tr>
				<th class="number">' . _('List Price by Air') . '</th>
				<td class="select">' . locale_number_format(($intlprice['price']/$currencyrate['rate'])*$intlprice['byair'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			</tr>';
			
			
			
		}
		if ($intlprice['showbysea'] == 'yes' || $_SESSION['AccessLevel'] == 10)
		{		
		echo '<tr>
				<th class="number">' . _('List Price by Sea') . '</th>
				<td class="select">' . locale_number_format(($intlprice['price']/$currencyrate['rate'])*$intlprice['bysea'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			</tr>';
		}
		if ($intlprice['showbylocal'] == 'yes' || $_SESSION['AccessLevel'] == 10)
		{
			echo '<tr>
				<th class="number">' . _('Selling Price') . '</th>
				<td class="select">' . locale_number_format(($intlprice['price']/$currencyrate['rate'])*$intlprice['bylocal'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			</tr>';
		}
		if(userHasPermission($db,"display_selling_average")){
			//average seling price
			$SQLsellingavg="SELECT AVG(invoicedetails.unitprice*(1-invoicedetails.discountpercent)) as avgsellingprice
 						FROM invoicedetails WHERE invoicedetails.stkcode = '$StockID'
 						ORDER BY invoicedetails.invoicedetailsindex DESC LIMIT 0,3";
			$ressellingavg=mysqli_query($db,$SQLsellingavg);
			$sellingavg=mysqli_fetch_assoc($ressellingavg)['avgsellingprice'];
			echo '<tr>
				<th class="number">' . _('Selling Average') . '</th>
				<td class="select">' . locale_number_format(round($sellingavg,0)) . '</td>';
			echo'</tr>';
		}
	} //end of if PricesSecuirty allows viewing of prices
	echo '</table>'; //end of first nested table
	// Item Category Property mod: display the item properties
	echo '<table class="selection" style="margin-left:130px;">';

	$sql = "SELECT stkcatpropid,
					label,
					controltype,
					defaultvalue
				FROM stockcatproperties
				WHERE categoryid ='" . $myrow['categoryid'] . "'
				AND reqatsalesorder =0
				ORDER BY stkcatpropid";
	$PropertiesResult = DB_query($sql, $db);
	$PropertyCounter = 0;
	$PropertyWidth = array();
	while ($PropertyRow = DB_fetch_array($PropertiesResult)) {
		$PropValResult = DB_query("SELECT value
									FROM stockitemproperties
									WHERE stockid='" . $StockID . "'
									AND stkcatpropid ='" . $PropertyRow['stkcatpropid']."'", $db);
		$PropValRow = DB_fetch_row($PropValResult);
		if (DB_num_rows($PropValResult)==0){
			$PropertyValue = _('Not Set');
		} else {
			$PropertyValue = $PropValRow[0];
		}
		echo '<tr >
				<th align="right" style="width:500px">' . $PropertyRow['label'] . ':</th>';
		switch ($PropertyRow['controltype']) {
			case 0:
			case 1:
				echo '<td class="select" style="width:500px">' . $PropertyValue;
			break;
			case 2; //checkbox
				echo '<td class="select" style="width:60px">';
				if ($PropertyValue == _('Not Set')){
					echo _('Not Set');
				} elseif ($PropertyValue == 1){
					echo _('Yes');
				} else {
					echo _('No');
				}
			break;
		} //end switch
	echo '</td></tr>';
	$PropertyCounter++;
} //end loop round properties for the item category
echo '</table></td>'; //end of Item Category Property mod
echo '<td style="width:15%; vertical-align:top">
			<table>'; //nested table to show QOH/orders
$QOH = 0;
switch ($myrow['mbflag']) {
	case 'A':
	case 'D':
	case 'K':
		$QOH = _('N/A');
		$QOO = _('N/A');
	break;
	case 'M':
	case 'B':
		$QOHResult = DB_query("SELECT sum(quantity)
						FROM locstock
						WHERE stockid = '" . $StockID . "'", $db);
		$QOHRow = DB_fetch_row($QOHResult);
		$QOH = locale_number_format($QOHRow[0], $myrow['decimalplaces']);
		$QOOSQL="SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd) AS QtyOnOrder
					FROM purchorders INNER JOIN purchorderdetails
					ON purchorders.orderno=purchorderdetails.orderno
					WHERE purchorderdetails.itemcode='" . $StockID . "'
					AND purchorderdetails.completed =0
					AND purchorders.status<>'Cancelled'
					AND purchorders.status<>'Pending'
					AND purchorders.status<>'Rejected'";
		$QOOResult = DB_query($QOOSQL, $db);
		if (DB_num_rows($QOOResult) == 0) {
			$QOO = 0;
		} else {
			$QOORow = DB_fetch_row($QOOResult);
			$QOO = $QOORow[0];
		}
		//Also the on work order quantities
		$sql = "SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS qtywo
				FROM woitems INNER JOIN workorders
				ON woitems.wo=workorders.wo
				WHERE workorders.closed=0
				AND woitems.stockid='" . $StockID . "'";
		$ErrMsg = _('The quantity on work orders for this product cannot be retrieved because');
		$QOOResult = DB_query($sql, $db, $ErrMsg);
		if (DB_num_rows($QOOResult) == 1) {
			$QOORow = DB_fetch_row($QOOResult);
			$QOO+= $QOORow[0];
		}
		$QOO = locale_number_format($QOO, $myrow['decimalplaces']);
	break;
}
$Demand = 0;
$DemResult = DB_query("SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
						FROM salesorderdetails INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
						WHERE salesorderdetails.completed=0
						AND salesorders.quotation=0
						AND salesorderdetails.stkcode='" . $StockID . "'", $db);
$DemRow = DB_fetch_row($DemResult);
$Demand = $DemRow[0];
$DemAsComponentResult = DB_query("SELECT  SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
									FROM salesorderdetails INNER JOIN salesorders
									ON salesorders.orderno = salesorderdetails.orderno
									INNER JOIN bom ON salesorderdetails.stkcode=bom.parent
									INNER JOIN stockmaster ON stockmaster.stockid=bom.parent
									WHERE salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
									AND bom.component='" . $StockID . "'
									AND stockmaster.mbflag='A'
									AND salesorders.quotation=0", $db);
$DemAsComponentRow = DB_fetch_row($DemAsComponentResult);
$Demand+= $DemAsComponentRow[0];
//Also the demand for the item as a component of works orders
$sql = "SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd)) AS woqtydemo
		FROM woitems INNER JOIN worequirements
		ON woitems.stockid=worequirements.parentstockid
		INNER JOIN workorders
		ON woitems.wo=workorders.wo
		AND woitems.wo=worequirements.wo
		WHERE  worequirements.stockid='" . $StockID . "'
		AND workorders.closed=0";
$ErrMsg = _('The workorder component demand for this product cannot be retrieved because');
$DemandResult = DB_query($sql, $db, $ErrMsg);
if (DB_num_rows($DemandResult) == 1) {
	$DemandRow = DB_fetch_row($DemandResult);
	$Demand+= $DemandRow[0];
}
echo '<tr style = "position:fixed; left: -999px">
		<th class="number" style="width:15%">' . _('Quantity On Hand') . ':</th>
		<td style="width:17%" class="select">' . $QOH . '</td>
	</tr>';
echo '<tr style = "position:fixed; left:-999px;">
		<th class="number" style="width:15%">' . _('Quantity Demand') . ':</th>
		<td style="width:17%" class="select">' . locale_number_format($Demand, $myrow['decimalplaces']) . '</td>
	</tr>';
echo '<tr style = "position:fixed; left:-999px;">
		<th class="number" style="width:15%">' . _('Quantity On Order') . ':</th>
		<td style="width:17%" class="select">' . $QOO . '</td>
	</tr>
	</table>'; //end of nested table
echo '</td>'; //end cell of master table

if (($myrow['mbflag'] == 'B' OR ($myrow['mbflag'] == 'M'))
	AND (in_array($SuppliersSecurity, $_SESSION['AllowedPageSecurityTokens']))){

	echo '<td style="width:50%" valign="top"><table>
			<tr><th style="width:50%">' . _('Supplier') . '</th>
				<th style="width:15%">' . _('Cost') . '</th>
				<th style="width:5%">' . _('Curr') . '</th>
				<th style="width:15%">' . _('Eff Date') . '</th>
				<th style="width:10%">' . _('Lead Time') . '</th>
				<th style="width:10%">' . _('Min Order Qty') . '</th>
				<th style="width:5%">' . _('Prefer') . '</th></tr>';
	$SuppResult = DB_query("SELECT suppliers.suppname,
									suppliers.currcode,
									suppliers.supplierid,
									purchdata.price,
									purchdata.effectivefrom,
									purchdata.leadtime,
									purchdata.conversionfactor,
									purchdata.minorderqty,
									purchdata.preferred,
									currencies.decimalplaces
								FROM purchdata INNER JOIN suppliers
								ON purchdata.supplierno=suppliers.supplierid
								INNER JOIN currencies
								ON suppliers.currcode=currencies.currabrev
								WHERE purchdata.stockid = '" . $StockID . "'
							ORDER BY purchdata.preferred DESC, purchdata.effectivefrom DESC", $db);
	while ($SuppRow = DB_fetch_array($SuppResult)) {
		echo '<tr><td class="select">' . $SuppRow['suppname'] . '</td>
					<td class="select">' . locale_number_format($SuppRow['price'] / $SuppRow['conversionfactor'], $SuppRow['decimalplaces']) . '</td>
					<td class="select">' . $SuppRow['currcode'] . '</td>
					<td class="select">' . ConvertSQLDate($SuppRow['effectivefrom']) . '</td>
					<td class="select">' . $SuppRow['leadtime'] . '</td>
					<td class="select">' . $SuppRow['minorderqty'] . '</td>';

		if ($SuppRow['preferred']==1) { //then this is the preferred supplier
			echo '<td class="select">' . _('Yes') . '</td>';
		} else {
			echo '<td class="select">' . _('No') . '</td>';
		}
		echo '<td class="select"><a href="' . $RootPath . '/PO_Header.php?NewOrder=Yes&amp;SelectedSupplier=' .
			$SuppRow['supplierid'] . '&amp;StockID=' . $StockID . '&amp;Quantity='.$SuppRow['minorderqty'].'&amp;LeadTime='.$SuppRow['leadtime'] . '">' . _('Order') . ' </a></td>';
		echo '</tr>';
	}
	echo '</table>';
	DB_data_seek($result, 0);
}
echo '</td></tr>';





echo '</table><br />'; // end first item details table

if (isset($_POST['UpdateBinLocations'])){
	foreach ($_POST as $PostVariableName => $Bin) {
		if (mb_substr($PostVariableName,0,11) == 'BinLocation') {
		echo	$sql = "UPDATE locstock SET bin='" . strtoupper($Bin) . "' WHERE loccode='" . mb_substr($PostVariableName,11) . "' AND stockid='" . $StockID . "'";
			$result = DB_query($sql, $db);
		}
	}
}
$result = DB_query("SELECT description,
						   units,
						   mbflag,
						   decimalplaces,
						   serialised,
						   controlled
					FROM stockmaster
					WHERE stockid='".$StockID."'",
					$db,
					_('Could not retrieve the requested item'),
					_('The SQL used to retrieve the items was'));

$myrow = DB_fetch_array($result);

$DecimalPlaces = $myrow['decimalplaces'];
$Serialised = $myrow['serialised'];
$Controlled = $myrow['controlled'];

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') .
	'" alt="" /><b>' . ' ' . $StockID . ' - ' . $myrow['description'] . ' : ' . _('in units of') . ' : ' . $myrow['units'] . '</b></p>';

$Its_A_KitSet_Assembly_Or_Dummy =False;
if ($myrow[2]=='K'){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	prnMsg( _('This is a kitset part and cannot have a stock holding') . ', ' . _('only the total quantity on outstanding sales orders is shown'),'info');
} elseif ($myrow[2]=='A'){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	prnMsg(_('This is an assembly part and cannot have a stock holding') . ', ' . _('only the total quantity on outstanding sales orders is shown'),'info');
} elseif ($myrow[2]=='D'){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	prnMsg( _('This is an dummy part and cannot have a stock holding') . ', ' . _('only the total quantity on outstanding sales orders is shown'),'info');
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre"><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo _('Stock Code') . ':<input type="text" data-type="no-illegal-chars" title ="'._('Input the stock code to inquire upon. Only alpha-numeric characters are allowed in stock codes with no spaces punctuation or special characters. Underscore or dashes are allowed.').'" placeholder="'._('Alpha-numeric only').'" required="required" name="StockID" size="21" value="' . $StockID . '" maxlength="20" />';

echo ' <input type="submit" name="ShowStatus" value="' . _('Show Stock Status') . '" />';
    if (!userHasPermission($db,"show_all_stores")AND!userHasPermission($db,"show_ps_stores")AND!userHasPermission($db,"show_is_stores"))
    {$sql = "SELECT locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				locstock.bin,
				locations.managed
		FROM locstock INNER JOIN locations
		ON locstock.loccode=locations.loccode
		WHERE locstock.stockid = '" . $StockID . "'";}

    if (userHasPermission($db,"show_all_stores"))
    {$sql = "SELECT locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				locstock.bin,
				locations.managed
		FROM locstock INNER JOIN locations
		ON locstock.loccode=locations.loccode
		WHERE locstock.stockid = '" . $StockID . "'";}
    if (userHasPermission($db,"show_ps_stores") AND !userHasPermission($db,"*"))
    {$sql = "SELECT locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				locstock.bin,
				locations.managed
		FROM locstock INNER JOIN locations
		ON locstock.loccode=locations.loccode
		WHERE locstock.stockid = '" . $StockID . "'
		AND (locstock.loccode LIKE '%PS%' OR locstock.loccode LIKE 'OS')"
    ;}
    if (userHasPermission($db,"show_is_stores") AND !userHasPermission($db,"*"))
    {$sql = "SELECT locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				locstock.bin,
				locations.managed
		FROM locstock INNER JOIN locations
		ON locstock.loccode=locations.loccode
		WHERE locstock.stockid = '" . $StockID . "'
		AND locstock.loccode NOT LIKE '%PS%'"
    ;}
/*if (userHasPermission($db,"show_ps_store"))
	$sql.= "AND locstock.loccode NOT LIKE '%PS%' ";
if (userHasPermission($db,"show_is_store"))
	$sql.= "AND locstock.loccode LIKE '%PS%' ";
$sql.=		"ORDER BY locations.locationname";*/

$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that was used to update the stock item and failed was');
$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

echo '<br />
		<table class="selection"><tbody>';

if ($Its_A_KitSet_Assembly_Or_Dummy == True){
	$TableHeader = '<tr>
						<th class="ascending">' . _('Location') . '</th>
						<th class="ascending">' . _('Demand') . '</th>
					</tr>';
} else {
	$TableHeader = '<tr>
						<th class="ascending">' . _('Location') . '</th>
						<th class="ascending">' . _('Bin Location') . '</th>
						<th class="ascending">' . _('Quantity On Hand') . '</th>
						
						<th class="ascending">' . _('In Transit') . '</th>
						<th class="ascending">' . _('Available') . '</th>
						<th class="ascending">' . _('On Order') . '</th>
					</tr>';
}
echo $TableHeader;
$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

$sql = "SELECT SUM(stockrequestitems.quantity) AS dem
				 FROM stockrequestitems INNER JOIN stockrequest
				 ON stockrequestitems.dispatchid = stockrequest.dispatchid
				 WHERE stockrequest.shiploc='" . $_SESSION['Request']->Location . "'
				 AND stockrequest.closed=0
				 AND stockrequest.authorised=1
				 AND stockrequestitems.stockid='" . $myrow['stockid'] . "'
				 ";

	$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
	$DemandResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($DemandResult)==1){
	  $DemandRow = DB_fetch_row($DemandResult);
	  $DemandQty =  $DemandRow[0];
	} else {
	  $DemandQty =0;
	}

	//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.
	$sql = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
			FROM salesorderdetails INNER JOIN salesorders
			ON salesorders.orderno = salesorderdetails.orderno
			INNER JOIN bom
			ON salesorderdetails.stkcode=bom.parent
			INNER JOIN stockmaster
			ON stockmaster.stockid=bom.parent
			WHERE salesorders.fromstkloc='" . $myrow['loccode'] . "'
			AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
			AND bom.component='" . $StockID . "'
			AND stockmaster.mbflag='A'
			AND salesorders.quotation=0";

	$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
	$DemandResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($DemandResult)==1){
		$DemandRow = DB_fetch_row($DemandResult);
		$DemandQty += $DemandRow[0];
	}

	//Also the demand for the item as a component of works orders

	$sql = "SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd)) AS woqtydemo
			FROM woitems INNER JOIN worequirements
			ON woitems.stockid=worequirements.parentstockid
			INNER JOIN workorders
			ON woitems.wo=workorders.wo
			AND woitems.wo=worequirements.wo
			WHERE workorders.loccode='" . $myrow['loccode'] . "'
			AND worequirements.stockid='" . $StockID . "'
			AND workorders.closed=0";

	$ErrMsg = _('The workorder component demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
	$DemandResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($DemandResult)==1){
		$DemandRow = DB_fetch_row($DemandResult);
		$DemandQty += $DemandRow[0];
	}

	if ($Its_A_KitSet_Assembly_Or_Dummy == False){

		$sql="SELECT SUM(purchorderdetails.quantityord*(CASE WHEN purchdata.conversionfactor IS NULL THEN 1 ELSE purchdata.conversionfactor END) -
							purchorderdetails.quantityrecd*(CASE WHEN purchdata.conversionfactor IS NULL THEN 1 ELSE purchdata.conversionfactor END))
			FROM purchorders LEFT JOIN purchorderdetails
			ON purchorders.orderno=purchorderdetails.orderno
			LEFT JOIN purchdata ON purchorders.supplierno=purchdata.supplierno
				AND purchorderdetails.itemcode=purchdata.stockid
			WHERE purchorderdetails.itemcode='" . $StockID . "'
			AND purchorders.intostocklocation='" . $myrow['loccode'] . "'
			AND (purchorders.status<>'Cancelled'
			AND purchorders.status<>'Pending'
			AND purchorders.status<>'Rejected'
			AND purchorders.status<>'Completed')";
		$ErrMsg = _('The quantity on order for this product to be received into') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg, $DbgMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO =  $QOORow[0];
		} else {
			$QOO = 0;
		}

		//Also the on work order quantities
		$sql = "SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS qtywo
				FROM woitems INNER JOIN workorders
				ON woitems.wo=workorders.wo
				WHERE workorders.closed=0
				AND workorders.loccode='" . $myrow['loccode'] . "'
				AND woitems.stockid='" . $StockID . "'";
		$ErrMsg = _('The quantity on work orders for this product to be received into') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg, $DbgMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO +=  $QOORow[0];
		}

		$InTransitSQL="SELECT SUM(shipqty-recqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $StockID . "'
							AND shiploc='".$myrow['loccode']."'";
		$InTransitResult=DB_query($InTransitSQL, $db);
		$InTransitRow=DB_fetch_array($InTransitResult);
		if ($InTransitRow['intransit']!='') {
			$InTransitQuantityOut=-$InTransitRow['intransit'];
		} else {
			$InTransitQuantityOut=0;
		}

		$InTransitSQL="SELECT SUM(-shipqty+recqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $StockID . "'
							AND recloc='".$myrow['loccode']."'";
		$InTransitResult=DB_query($InTransitSQL, $db);
		$InTransitRow=DB_fetch_array($InTransitResult);
		if ($InTransitRow['intransit']!='') {
			$InTransitQuantityIn=-$InTransitRow['intransit'];
		} else {
			$InTransitQuantityIn=0;
		}

		if (($InTransitQuantityIn+$InTransitQuantityOut) < 0) {
			$Available = $myrow['quantity'] - $DemandQty + ($InTransitQuantityIn+$InTransitQuantityOut);
		} else {
			$Available = $myrow['quantity'] - $DemandQty;
		}

		echo '<td>' . $myrow['locationname'] . '</td>
			  <td><input type="text" name="BinLocation' . $myrow['loccode'] . '" value="' . $myrow['bin'] . '" maxlength="10" size="11" onchange="ReloadForm(UpdateBinLocations)"/></td>';

		printf('<td class="number">%s</td>
				
				
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>',
				locale_number_format($myrow['quantity'], $DecimalPlaces),
				
				
				locale_number_format($InTransitQuantityIn+$InTransitQuantityOut, $DecimalPlaces),
				locale_number_format($Available, $DecimalPlaces),
				locale_number_format($QOO, $DecimalPlaces)
				);

		if ($Serialised ==1){ /*The line is a serialised item*/

			echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Serialised=Yes&amp;Location=' . $myrow['loccode'] . '&amp;StockID=' .$StockID . '">' . _('Serial Numbers') . '</tr>';
		} elseif ($Controlled==1){
			echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Location=' . $myrow['loccode'] . '&amp;StockID=' .$StockID . '">' . _('Batches') . '</a></td></tr>';
		}else{
			echo '</tr>';
		}

	} else {
	/* It must be a dummy, assembly or kitset part */

		printf('<td>%s</td>
				<td class="number">%s</td>
				</tr>',
				$myrow['locationname'],
				locale_number_format($DemandQty, $DecimalPlaces));
	}
//end of page full new headings if
}
//end of while loop
echo '</tbody><tr>
		<td></td>
		<td><input type="submit" name="UpdateBinLocations" value="' . _('Update Bins') . '" /></td>
	</tr>
	</table>';
//stock held status
$SQLIssuance='
SELECT salesperson, SUM(issued) as issued FROM `stockissuance` WHERE stockid = "'.$StockID.'" 
AND issued>0
GROUP BY salesperson

';
$SQLIssuanceResult=DB_query($SQLIssuance,$db);
echo'<h4>Stock Held Status</h4>
<table>
<tr><td><b>Sales Person</b></td><td><b>Quantity Held</b></td></tr>

';
while($SQLIssuanceRow=DB_fetch_array($SQLIssuanceResult))
{
echo'
<tr><td>'.$SQLIssuanceRow['salesperson'].'</td><td>'.$SQLIssuanceRow['issued'].'</td></tr>

';
}
echo'</table>';
if (isset($_GET['DebtorNo'])){
	$DebtorNo = trim(mb_strtoupper($_GET['DebtorNo']));
} elseif (isset($_POST['DebtorNo'])){
	$DebtorNo = trim(mb_strtoupper($_POST['DebtorNo']));
} elseif (isset($_SESSION['CustomerID'])){
	$DebtorNo=$_SESSION['CustomerID'];
}

if ($DebtorNo) { /* display recent pricing history for this debtor and this stock item */

	$sql = "SELECT stockmoves.trandate,
				stockmoves.qty,
				stockmoves.price,
				stockmoves.discountpercent
			FROM stockmoves
			WHERE stockmoves.debtorno='" . $DebtorNo . "'
				AND stockmoves.type=10
				AND stockmoves.stockid = '" . $StockID . "'
				AND stockmoves.hidemovt=0
			ORDER BY stockmoves.trandate DESC";

	/* only show pricing history for sales invoices - type=10 */

	$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because') . ' - ';
	$DbgMsg = _('The SQL that failed was');

	$MovtsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	$k=1;
	while ($myrow=DB_fetch_array($MovtsResult)) {
	  if ($LastPrice != $myrow['price']
			OR $LastDiscount != $myrow['discount']) { /* consolidate price history for records with same price/discount */
	    if (isset($qty)) {
	    	$DateRange=ConvertSQLDate($FromDate);
	    	if ($FromDate != $ToDate) {
	        	$DateRange .= ' - ' . ConvertSQLDate($ToDate);
	     	}
	    	$PriceHistory[] = array($DateRange, $qty, $LastPrice, $LastDiscount);
	    	$k++;
	    	if ($k > 9) {
                  break; /* 10 price records is enough to display */
                }
	    	if ($myrow['trandate'] < FormatDateForSQL(DateAdd(date($_SESSION['DefaultDateFormat']),'y', -1))) {
	    	  break; /* stop displaying pirce history more than a year old once we have at least one  to display */
   	        }
	    }
	    $LastPrice = $myrow['price'];
	    $LastDiscount = $myrow['discountpercent'];
	    $ToDate = $myrow['trandate'];
	    $qty = 0;
	  }
	  $qty += $myrow['qty'];
	  $FromDate = $myrow['trandate'];
	}
	if (isset($qty)) {
		$DateRange = ConvertSQLDate($FromDate);
		if ($FromDate != $ToDate) {
	   		$DateRange .= ' - '.ConvertSQLDate($ToDate);
		}
		$PriceHistory[] = array($DateRange, $qty, $LastPrice, $LastDiscount);
	}
	if (isset($PriceHistory)) {
	  echo '<br />
			<table class="selection">
			<tr>
				<th colspan="4"><font color="navy" size="2">' . _('Pricing history for sales of') . ' ' . $StockID . ' ' . _('to') . ' ' . $DebtorNo . '</font></th>
			</tr><tbody>';
	  $TableHeader = '<tr>
						<th class="ascending">' . _('Date Range') . '</th>
						<th class="ascending">' . _('Quantity') . '</th>
						<th class="ascending">' . _('Price') . '</th>
						<th class="ascending">' . _('Discount') . '</th>
					</tr>';

	  $j = 0;
	  $k = 0; //row colour counter

	  foreach($PriceHistory as $PreviousPrice) {
		$j--;
		if ($j < 0 ){
			$j = 11;
			echo $TableHeader;
		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

			printf('<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s%%</td>
					</tr>',
					$ph[0],
					locale_number_format($PreviousPrice[1],$DecimalPlaces),
					locale_number_format($PreviousPrice[2],$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($PreviousPrice[3]*100,2));
	  }
	 echo '</tbody></table>';
	 }
	//end of while loop
	else {
	  echo '<p>' . _('No history of sales of') . ' ' . $StockID . ' ' . _('to') . ' ' . $DebtorNo;
	}
}//end of displaying price history for a debtor
echo '<br /><a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '">' . _('Show Movements') . '</a>
	<br /><a href="' . $RootPath . '/StockUsage.php?StockID=' . $StockID . '">' . _('Show Usage') . '</a>
	<br /><a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Sales Orders') . '</a>
	<br /><a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a>';
if ($Its_A_KitSet_Assembly_Or_Dummy ==False){
	echo '<br /><a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Purchase Orders') . '</a>';
}

echo '</div></form>';
echo '<br/><form method="post" action="reorder/reorderRequest.php"><input type="hidden" name="FormID" value="'.$_SESSION['FormID'].'"/><input type="hidden" name="stockid" value="'.$StockID.'"/><input type="submit" value="ReOrder Request"></form>';
echo '<br/><a class="btn btn-primary" target="_blank" href="reorder/reorderItemHistory.php?stockid='.$StockID.'">Reorder Item History</a>';
$sql = "select * from itemcomments where itemcode = '".$StockID."'";
$result = DB_query($sql,$db);

echo '
<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="GET">
<table class="selection">
<tr><td> Comments </td></tr>';

while ($row = DB_fetch_array($result))
{
	echo '<tr><td>'.$row['comment'].'<td><td>'.$row['username'].'<td> <td>'.$row['time'].'<td></tr>';
}


echo '<tr>


<td><textarea name = "comment" rows="4" cols="80"></textarea><td>

</tr>

<tr>


<td align="center"><input type = "submit" name = "SubmitComment" value = "submit"></textarea><td>

</tr>
</table>

</form>
';

if (isset($_GET['SubmitComment']))
{
	$sql = "insert into itemcomments(itemcode,comment,username)
	
	values('".$StockID."','".$_GET['comment']."','".$_SESSION['UsersRealName']."')";
	
	DB_query($sql,$db);





}	







echo '<table width="90%"><tr>
		<th style="width:33%">' . _('Item Inquiries') . '</th>
		<th style="width:33%">' . _('Item Transactions') . '</th>
		<th style="width:33%">' . _('Item Maintenance') . '</th>
	</tr>';
echo '<tr><td valign="top" class="select">';
/*Stock Inquiry Options */
echo '<a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '">' . _('Show Stock Movements') . '</a><br />';
if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
	echo '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $StockID . '">' . _('Show Stock Status') . '</a><br />';
	echo '<a href="' . $RootPath . '/StockUsage.php?StockID=' . $StockID . '">' . _('Show Stock Usage') . '</a><br />';
}
echo '<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '">' . '</a><br />';
echo '<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' .  '</a><br />';
if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
	echo '<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $StockID . '">' .'</a><br />';
	echo '<a href="' . $RootPath . '/PO_SelectPurchOrder.php?SelectedStockItem=' . $StockID . '">' . '</a><br />';
	echo '<a href="' . $RootPath . '/' . $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg">' . _('Show Part Picture (if available)') . '</a><br />';
}
if ($Its_A_Dummy == False) {
//	echo '<a  href="' . $RootPath . '/BOMInquiry.php?StockID=' . $StockID . '">' . _('View Costed Bill Of Material') . '</a><br />';
//	echo '<a href="' . $RootPath . '/WhereUsedInquiry.php?StockID=' . $StockID . '">' . '</a><br />';
}
if ($Its_A_Labour_Item == True) {
	echo '<a href="' . $RootPath . '/WhereUsedInquiry.php?StockID=' . $StockID . '">' . _('Where This Labour Item Is Used') . '</a><br />';
}
wikiLink('Product', $StockID);
echo '</td><td valign="top" class="select">';
/* Stock Transactions */
if ($Its_A_Kitset_Assembly_Or_Dummy == false) {
	echo '<a href="' . $RootPath . '/StockAdjustments.php?StockID=' . $StockID . '">' . _('Quantity Adjustments') . '</a><br />';
	
	echo '<a href="' . $RootPath . '/StockTransfers.php?StockID=' . $StockID . '&amp;NewTransfer=true">' . _('Location Transfers') . '</a><br />';
	echo '<a href="javascript:void(0);"
 NAME="My Window Name"  title=" My title here "
 onClick=window.open("quotationhistory.php/?stockid='.$StockID.'","Ratting","width=550,height=170,0,status=0,scrollbars=1");>Quotation History</a><br/><a href="javascript:void(0);"
 NAME="My Window Name"  title=" My title here "
 onClick=window.open("itemHistory.php/?stockid='.$StockID.'","Ratting","width=550,height=170,0,status=0,scrollbars=1");>Item History</a><br/>';
				
	//show the item image if it has been uploaded
	 if (function_exists('imagecreatefromjpg')){
		if ($_SESSION['ShowStockidOnImages'] == '0'){
			$StockImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
								'&amp;StockID='.urlencode($StockID).
								'&amp;text='.
								'&amp;width=100'.
								'&amp;height=100'.
								'" alt="" />';
		} else {
			$StockImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
								'&amp;StockID='.urlencode($StockID).
								'&amp;text='. $StockID .
								'&amp;width=100'.
								'&amp;height=100'.
								'" alt="" />';
		}
	} else {
		if( isset($StockID) AND file_exists($_SESSION['part_pics_dir'] . '/' .$StockID.'.jpg') ) {
			$StockImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg" height="100" width="100" />';
		} else {
			$StockImgLink = _('No Image');
		}
	}

	echo '<div class="centre">' . $StockImgLink . '</div>';
	if (($myrow['mbflag'] == 'B')
		AND (in_array($SuppliersSecurity, $_SESSION['AllowedPageSecurityTokens']))
		AND $myrow['discontinued']==0){
		echo '<br />';
		$SuppResult = DB_query("SELECT suppliers.suppname,
										suppliers.supplierid,
										purchdata.preferred,
										purchdata.minorderqty,
										purchdata.leadtime
									FROM purchdata INNER JOIN suppliers
									ON purchdata.supplierno=suppliers.supplierid
									WHERE purchdata.stockid='" . $StockID . "'
									ORDER BY purchdata.effectivefrom DESC", $db);
		$LastSupplierShown = "";
		while ($SuppRow = DB_fetch_array($SuppResult)) {
			if ($LastSupplierShown != $SuppRow['supplierid']){
				if (($myrow['eoq'] < $SuppRow['minorderqty'])) {
					$EOQ = $SuppRow['minorderqty'];
				} else {
					$EOQ = $myrow['eoq'];
				}
				echo '<a href="' . $RootPath . '/PO_Header.php?NewOrder=Yes' . '&amp;SelectedSupplier=' . $SuppRow['supplierid'] . '&amp;StockID=' . $StockID . '&amp;Quantity='.$EOQ.'&amp;LeadTime='.$SuppRow['leadtime'].'">' .  _('Purchase this Item from') . ' ' . $SuppRow['suppname'] . '</a>
				<br />';
				$LastSupplierShown = $SuppRow['supplierid'];
			}
			/**/
		} /* end of while */
	} /* end of $myrow['mbflag'] == 'B' */
} /* end of ($Its_A_Kitset_Assembly_Or_Dummy == False) */
echo '</td><td valign="top" class="select">';
/* Stock Maintenance Options */
echo '<a href="' . $RootPath . '/Stocks.php?">' . _('Insert New Item') . '</a><br />';
echo '<a href="' . $RootPath . '/Stocks.php?StockID=' . $StockID . '">' . _('Modify Item Details') . '</a><br />';
if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
	echo '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $StockID . '">' . _('Maintain Reorder Levels') . '</a><br />';
	if(userHasPermission($db,"stock_cost_update")){
		echo '<a href="' . $RootPath . '/StockCostUpdate.php?StockID=' . $StockID . '">' ._('StockCostUpdate') .  '</a><br />';
	}
	if ($_SESSION['AccessLevel'] == 10)
	echo '<a href="' . $RootPath . '/IntlStockCostUpdate.php?StockID=' . $StockID . '">' ._('International Cost Update') .  '</a><br />';
		
	echo '<a href="' . $RootPath . '/PurchData.php?StockID=' . $StockID . '">' .'</a><br />';
}
if ($Its_A_Labour_Item == True) {
//	echo '<a href="' . $RootPath . '/StockCostUpdate.php?StockID=' . $StockID . '">' . _('Maintain Standard Cost') . '</a><br />';
}
if (!$Its_A_Kitset) {
	echo '<a href="' . $RootPath . '/Prices.php?Item=' . $StockID . '">' . '</a><br />';
	if (isset($_SESSION['CustomerID'])
		AND $_SESSION['CustomerID'] != ''
		AND mb_strlen($_SESSION['CustomerID']) > 0) {
		//echo '<a href="' . $RootPath . '/Prices_Customer.php?Item=' . $StockID . '">' . _('Special Prices for customer') . ' - ' . $_SESSION['CustomerID'] . '</a><br />';
	}
	echo '<a href="' . $RootPath . '/DiscountCategories.php?StockID=' . $StockID . '">' . '</a><br />';
   echo '<a href="' . $RootPath . '/StockClone.php?OldStockID=' . $StockID . '">' . _('Clone This Item') . '</a><br />';
}
echo '</td></tr></table>';
} else {
	// options (links) to pages. This requires stock id also to be passed.
	echo '<table width="90%" cellpadding="4">';
	echo '<tr>
		<th style="width:33%">' . _('Item Inquiries') . '</th>
		<th style="width:33%">' . _('Item Transactions') . '</th>
		<th style="width:33%">' . _('Item Maintenance') . '</th>
	</tr>';
	echo '<tr><td class="select">';
	/*Stock Inquiry Options */
	echo '</td><td class="select">';
	/* Stock Transactions */
	echo '</td><td class="select">';
	/*Stock Maintenance Options */
	echo '<a href="' . $RootPath . '/Stocks.php?">' . _('Insert New Item') . '</a><br />';
	echo '</td></tr></table>';
} // end displaying item options if there is one and only one record
echo '<form name = "searchform" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="GET">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Inventory Items'). '</p>';
echo '<table class="selection"><tr>';
echo '<td>' . _('In Stock Category') . ':';
echo '<select name="StockCat" onchange="ReloadForm(searchform.UpdateCategories)">';
if (!isset($_GET['StockCat'])) {
	$_GET['StockCat'] ='';
}
if ($_GET['StockCat'] == 'All') {
	echo '<option selected="selected" value="All">' . _('All') . '</option>';
} else {
	echo '<option value="All">' . _('All') . '</option>';
}
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['categoryid'] == $_GET['StockCat']) {
		echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	}
}
echo '</select></td>';
echo '<td>' . _('Brand') . ':';
echo '<select name="brand">';



/*
if (!isset($_GET['brand'])) {
	$_GET['brand'] ='';
}
*/
if ($_GET['brand'] == 'All') {
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
if (isset($_GET['Keywords'])) {
	echo '<input type="text" autofocus="autofocus" name="Keywords" value="' . $_GET['Keywords'] . '" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
} else {
	echo '<input type="text" autofocus="autofocus" name="Keywords" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
}
*/

echo '<td><b>' . _('') . ' ' . '</b>' . _('Or Enter partial') . ' <b>' . _('Stock Code/Part number/Manufacturer Code') . '</b>:</td>';

echo '<td>';

if (isset($_GET['StockCode'])) {
	echo '<input type="text" name="StockCode" value="' . $_GET['StockCode'] . '" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="40" />';
} else {
	echo '<input type="text" name="StockCode" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="40" />';
}

echo '</tr></table><br />';
if (isset($_GET['StockCat']) and $_GET['StockCat'] != 'All')
{
$SQL = 'SELECT * FROM `stockcatproperties` WHERE categoryid like "'.$_GET['StockCat'].'"';
$result3 = DB_query($SQL, $db);
echo '<table> <tr><td><b>Property Name</b></td><td><b>Value</b></td></tr>


';
 while ($myrow3 = DB_fetch_array($result3))
 {
echo '<tr><td>'.$myrow3['label'].'</td>';

if ($myrow3['controltype'] == 1)
{
$OptionValues = explode(',',$myrow3['defaultvalue']);
			echo '<td><select name="PropValue[]" size = "1">';
	echo '<option value="">' . _('All') . '</option>';

			foreach ($OptionValues as $PropertyOptionValue){
				if ($PropertyOptionValue == $PropertyValue){
				
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
 
echo '<td>Value Range '.$myrow3['minimumvalue'].' to '.$myrow3['maximumvalue'].'
<input name="PropValueMin[]"type = "text"><= x <= <input name="PropValueMax[]"type = "text"></td></tr>';

}
if ($myrow3['controltype'] == 0 and ($myrow3['minimumvalue'] == 0 and $myrow3['maximumvalue'] == 0))
//echo '<tr><td>'.$myrow3['label'].'</td><td>'.$myrow3['minimumvalue']."<=".'<input type = "text" name = "PropValue[]">'.'<= '.$myrow3['maximumvalue'].'</td>';
{echo '<td><input name="PropValueText[]" type = "text"></td></tr>';


}
}
echo '</table>';




}
echo '<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />';
	
echo '<div class="centre"><input type="submit" name="Search" value="' . _('Search Now') . '" /></div><br />';
echo '</div>
      </form>';
// query for list of record(s)
if(isset($_GET['Go']) OR isset($_GET['Next']) OR isset($_GET['Previous'])) {
	$_GET['Search']='Search';
}
if (isset($_GET['Search']) OR isset($_GET['Go']) OR isset($_GET['Next']) OR isset($_GET['Previous'])) {
	if (!isset($_GET['Go']) AND !isset($_GET['Next']) AND !isset($_GET['Previous'])) {
		// if Search then set to first page
		$_GET['PageOffset'] = 1;
	}
	if ($_GET['StockCode']) {
		prnMsg (_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	
	if (isset($_GET['StockCode'])) {
		//insert wildcard characters in spaces
		$SearchString2 = '%' . str_replace(' ', '%', $_GET['StockCode']) . '%';
		if ($_GET['StockCat'] == 'All') 
		{
		if($_SESSION['brand'] == 'All' )
		{
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.conditionID,
						stockmaster.mbflag,
						stockmaster.discontinued,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces,
						manufacturers.manufacturers_name
						FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
						INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
						WHERE stockmaster.mnfCode " . LIKE . " '%" . $SearchString2 . "%'
					or (stockmaster.stockid " . LIKE . " '%" . $SearchString2 . "%'
					AND stockmaster.stockid NOT LIKE '%\t%'
					)
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY stockmaster.discontinued, stockmaster.stockid";
						}
						else
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.conditionID,
						stockmaster.mbflag,
						stockmaster.discontinued,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces,
						manufacturers.manufacturers_name
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid=locstock.stockid
					INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
					WHERE brand ='" . $_SESSION['brand'] . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY stockmaster.discontinued, stockmaster.stockid";
					
		
		}  else {
		if($_GET['brand'] == 'All' )
		{
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.conditionID,
						stockmaster.mbflag,
						stockmaster.discontinued,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces,
						manufacturers.manufacturers_name
						FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
						INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
						WHERE 
						categoryid='" . $_GET['StockCat'] . "'
						AND stockmaster.stockid NOT LIKE '%\t%'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY stockmaster.discontinued, stockmaster.stockid";
						}
						else
						{
						
						$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.conditionID,
						stockmaster.mbflag,
						stockmaster.discontinued,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces,
						manufacturers.manufacturers_name
						FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
						INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
						WHERE brand='" . $_SESSION['brand'] . "'
						AND categoryid='" . $_GET['StockCat'] . "'
						AND stockmaster.stockid NOT LIKE '%\t%'
					GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY stockmaster.discontinued, stockmaster.stockid";
						
						}
		}
	} elseif (isset($_GET['StockCode'])) 
	{
		$_GET['StockCode'] = mb_strtoupper($_GET['StockCode']);
		
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.conditionID,
						stockmaster.mbflag,
						stockmaster.discontinued,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces,
						manufacturers.manufacturers_name
					FROM stockmaster 
					INNER JOIN locstock ON stockmaster.stockid=locstock.stockid
					INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
					WHERE stockmaster.stockid " . LIKE . " '%" . $SearchString2 . "%'
					or (stockmaster.mnfCode " . LIKE . " '%" . $SearchString2 . "%'
					AND stockmaster.stockid NOT LIKE '%\t%'
					)GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY stockmaster.discontinued, stockmaster.stockid";
		}
	} elseif (!isset($_GET['StockCode']) AND !isset($_GET['keyproperties'])) 
	{
		if ($_GET['StockCat'] == 'All') 
		{
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.conditionID,
						stockmaster.mbflag,
						stockmaster.discontinued,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces,
						manufacturers.manufacturers_name
					FROM stockmaster 
					INNER JOIN locstock ON stockmaster.stockid=locstock.stockid
					INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
					WHERE brand='" . $_SESSION['brand'] . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY stockmaster.discontinued, stockmaster.stockid";
					}
					else{$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.conditionID,
						stockmaster.mbflag,
						stockmaster.discontinued,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces,
						manufacturers.manufacturers_name
					FROM stockmaster 
					INNER JOIN locstock ON stockmaster.stockid=locstock.stockid
					INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
					WHERE categoryid like'" . $_GET['StockCat'] . "'
					AND brand like '" . $_SESSION['brand'] . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY stockmaster.discontinued, stockmaster.stockid";
						
					
					
					}
					
		}

	$count = 0;

	if (isset($_GET['StockCat']) and $_GET['StockCat']!= 'All')
	{
	foreach ($_GET['PropValue'] as $val)
					{
						if ($val !='')
								$count++;
					}
					}
				//	echo $count."<br>";
if ($count>0 AND $_SESSION['brand']!='All')
	{
	$SQL2 = "CREATE VIEW tempview as SELECT stockmaster.stockid, count(stockmaster.stockid) as count 
					FROM stockmaster,stockitemproperties
					WHERE stockmaster.stockid = stockitemproperties.stockid
						and stockmaster.categoryid like'" . $_GET['StockCat'] . "'
					AND brand like '" . $_SESSION['brand'] . "'
					
					and( " 
					;
					if (isset($_GET['StockCat']) and $_GET['StockCat']!= 'All')
	{
					$count = 0;
					foreach ($_GET['PropValue'] as $value)
					{
					if ($value != '')
					{				
			$count++;
				if ($count <= 1)
	$SQL2.="		stockitemproperties.value like '".$value."'"
					;
				else
					$SQL2.="		or stockitemproperties.value like '%".$value."%'"
					;
					}
					}
	}
					$SQL2.= "";
					
					if (isset($_GET['StockCat']) and $_GET['StockCat']!= 'All')
	{
					
					foreach ($_GET['PropValueText'] as $value)
					{
					if ($value != '')
					{				
			$count++;
				if ($count <= 1)
	$SQL2.="		stockitemproperties.value like '%".$value."%'"
					;
				else
					$SQL2.="		or stockitemproperties.value like '%".$value."%'"
					;
					}
					}
	}
	if (isset($_GET['StockCat']) and $_GET['StockCat']!= 'All')
	{
					
					for ($i = 0; $i<count($_GET['PropValueMin']);$i++)
					{
					if ($_GET['PropValueMin'][$i] != '' && $_GET['PropValueMax'][$i] != '')
					{				
			$count++;
				if ($count <= 1)
	$SQL2.="		stockitemproperties.value < ".intval($_GET['PropValueMax'][$i])."

and 		stockitemproperties.value  > ".intval($_GET['PropValueMin'][$i])."
"
				
					
					;
				else
					$SQL2.="		or stockitemproperties.value like '%".$value."%'"
					;
					}
					}
	}
					$SQL2.= ") GROUP BY stockitemproperties.stockid
					HAVING count=".$count."
					";
					}
else
if ($count>0)
	{
	$SQL2 = "CREATE VIEW tempview as SELECT stockmaster.stockid, count(stockmaster.stockid) as count 
					FROM stockmaster,stockitemproperties
					WHERE stockmaster.stockid = stockitemproperties.stockid
						and stockmaster.categoryid like'" . $_GET['StockCat'] . "'
					
					
					and( " 
					;
					if (isset($_GET['StockCat']) and $_GET['StockCat']!= 'All')
	{
					$count = 0;
					foreach ($_GET['PropValue'] as $value)
					{
					if ($value != '')
					{				
			$count++;
				if ($count <= 1)
	$SQL2.="		stockitemproperties.value like '".$value."'"
					;
				else
					$SQL2.="		or stockitemproperties.value like '%".$value."%'"
					;
					}
					}
	}
					$SQL2.= "";
					
					if (isset($_GET['StockCat']) and $_GET['StockCat']!= 'All')
	{
					
					foreach ($_GET['PropValueText'] as $value)
					{
					if ($value != '')
					{				
			$count++;
				if ($count <= 1)
	$SQL2.="		stockitemproperties.value like '%".$value."%'"
					;
				else
					$SQL2.="		or stockitemproperties.value like '%".$value."%'"
					;
					}
					}
	}
	if (isset($_GET['StockCat']) and $_GET['StockCat']!= 'All')
	{
					
					for ($i = 0; $i<count($_GET['PropValueMin']);$i++)
					{
					if ($_GET['PropValueMin'][$i] != '' && $_GET['PropValueMax'][$i] != '')
					{				
			$count++;
				if ($count <= 1)
	$SQL2.="		stockitemproperties.value < ".intval($_GET['PropValueMax'][$i])."

and 		stockitemproperties.value  > ".intval($_GET['PropValueMin'][$i])."
"
				
					
					;
				else
					$SQL2.="		or stockitemproperties.value like '%".$value."%'"
					;
					}
					}
	}
					$SQL2.= ") GROUP BY stockitemproperties.stockid
					HAVING count=".$count."
					";
					}
	
		DB_query($SQL2,$db) ;
		
//	echo $SQL2;
		
	$SQL2 = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.conditionID,
						stockmaster.mbflag,
						stockmaster.discontinued,
						
						stockmaster.units,
						stockmaster.decimalplaces
						
					FROM stockmaster
					INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
					WHERE stockmaster.stockid 
					IN
					(
						SELECT stockid from tempview
					
					)
					AND stockmaster.stockid NOT LIKE '%\t%'
					";

	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL that returned an error was');
//	echo $count;
	if ($count > 0)
	{//echo	 $SQL2;
	$SearchResult = DB_query($SQL2, $db, $ErrMsg, $DbgMsg);
	}
	else
	$SearchResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
	
	if ($count>0)
	{
		$SQLDrop = "DROP VIEW tempview";
	DB_query($SQLDrop,$db);
	}
	
	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(_('No stock items were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_GET['Search']);

/* end query for list of records */
/* display list if there is more than one record */
if (isset($SearchResult) AND !isset($_GET['Select']) AND !isset($_GET['UpdateCategories'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="GET">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$ListCount = DB_num_rows($SearchResult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_GET['Next'])) {
			if ($_GET['PageOffset'] < $ListPageMax) {
				$_GET['PageOffset'] = $_GET['PageOffset'] + 1;
			}
		}
		if (isset($_GET['Previous'])) {
			if ($_GET['PageOffset'] > 1) {
				$_GET['PageOffset'] = $_GET['PageOffset'] - 1;
			}
		}
		if ($_GET['PageOffset'] > $ListPageMax) {
			$_GET['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;&nbsp;' . $_GET['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_GET['PageOffset']) {
					echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
				} else {
					echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />
				<input type="hidden" name="Keywords" value="'.$_GET['Keywords'].'" />
				<input type="hidden" name="StockCat" value="'.$_GET['StockCat'].'" />
				<input type="hidden" name="StockCode" value="'.$_GET['StockCode'].'" />
				<br />
				</div>';
		}
		echo '<table id="ItemSearchTable" class="selection" >';
		$TableHeader = '<tr>
							<th>' . _('Stock Status') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Brand') . '</th>
							<th class="ascending">' . _('Manufacturers Code') . '</th>
							<th class="ascending">' . _('Manufacturers Part Number') . '</th>
							
							<th class="ascending">' . _('Description') . '</th>
							<th style = "position:absolute; left:-999px">' . _('Total Qty On Hand') . '</th>
							<th style = "position:absolute; left:-999px">' . _('Units') . '</th>
						</tr>';
		echo $TableHeader;
		$j = 1;
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
		if (DB_num_rows($SearchResult) <> 0) {
			DB_data_seek($SearchResult, ($_GET['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($myrow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
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

			echo '<td>' . $ItemStatus . '</td>';
			
			if($myrow['conditionID']==1)
			echo'<td><a style="color:black" href = "SelectProduct.php?Select=' . $myrow['stockid'] . '" target = "_blank" >' . $myrow['stockid'] . '</a></td>';
			if($myrow['conditionID']==2)
			echo'<td><a style="color:blue" href = "SelectProduct.php?Select=' . $myrow['stockid'] . '" target = "_blank" >' . $myrow['stockid'] . '</a></td>';
			if($myrow['conditionID']==3)
			echo'<td><a style="color:yellow" href = "SelectProduct.php?Select=' . $myrow['stockid'] . '" target = "_blank" >' . $myrow['stockid'] . '</a></td>';
			if($myrow['conditionID']==4)
			echo'<td><a style="color:orange" href = "SelectProduct.php?Select=' . $myrow['stockid'] . '" target = "_blank" >' . $myrow['stockid'] . '</a></td>';
			echo '<td>'.$myrow['manufacturers_name'].'</td>';	
			echo'<td title="'. $myrow['mnfCode'] . '">' . $myrow['mnfCode'] . '</td>
				<td title="'. $myrow['mnfpno'] . '">' . $myrow['mnfpno'] . '</td>
				
				<td title="'. $myrow['longdescription'] . '">' . $myrow['description'] . '</td>
				<td style = "position:absolute; left:-999px" class="number">' . $qoh . '</td>
				<td>' . $myrow['units'] . '</td>
				<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'].'">' . _('View') . '</a></td>
				</tr>';
/*
			$j++;

			if ($j == 20 AND ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $TableHeader;
			}
			*/
			$RowIndex = $RowIndex + 1;
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

include ('includes/footer.inc');
?>
