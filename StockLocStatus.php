<?php

/* $Id: StockLocStatus.php 6033 2013-06-24 07:36:26Z daintree $*/

include('includes/session.inc');

$Title = _('All Stock Status By Location/Category');

include('includes/header.inc');
if (isset($_GET['updatereorderlevel']))
{
	$arrreorderlevel = $_GET['reorderlevel'];
	  $arrstockid = $_GET['stockid'];
	  
	  for ($i = 0; $i<sizeof($_GET['reorderlevel']); $i++)
	  {
		//echo $arrreorderlevel[$i];
		
		//echo  $tempreorderlevel = "'reorderlevel[".$i."]'";
		//echo $tempreorderlevel = $_GET[$tempreorderlevel];
		 //echo  $tempstockid = "'stockid[".$i."]'";
		 
		$SQL = 'UPDATE locstock SET reorderlevel = '.$arrreorderlevel[$i].' WHERE 
		  stockid = "'.$arrstockid[$i].'" AND loccode = "'.$_GET['StockLocation'].'"';
		  
		  DB_query($SQL,$db,true);
	  }
}
if (!isset($_GET['PageOffset'])) {
	$_GET['PageOffset'] = 1;
} else {
	if ($_GET['PageOffset'] == 0) {
		$_GET['PageOffset'] = 1;
	}
}
if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
}

if (isset($_GET['substore']))
$_SESSION['substore'] = $_GET['substore'];
//$_GET['substore'] = $_SESSION['substore'];
if (isset($_GET['user']))
$_SESSION['user'] = $_GET['user'];
$_GET['user'] = $_SESSION['user'];
if (isset($_GET['brand']))
$_SESSION['brand'] = $_GET['brand'];
//else $_SESSION['brand'] = "";
//$_GET['brand'] = $_SESSION['brand'];
if (isset($_GET['StockCat']))
$_SESSION['StockCat'] = $_GET['StockCat'];
//else $_SESSION['StockCat'] = "";
//$_GET['StockCat'] = $_SESSION['StockCat'];

echo '<form name = "searchform" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="GET">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$sql = "SELECT loccode,
    	       locationname
    	FROM locations";
$resultStkLocs = DB_query($sql,$db);
 $sql = "SELECT substoreid,
    	       description
    	FROM substores where locid = '".$_GET['StockLocation']."'";
$resultSubstores = DB_query($sql,$db);

 $sql = "SELECT realname
    	FROM www_users where defaultlocation like '%".$_GET['StockLocation']."%'";
$resultUsers = DB_query($sql,$db);
if(isset($_GET['UpdateCategories']))
{

if ($_GET['StockCat'] == '')
{
	$SQL = "SELECT * from manufacturers";
}
else
{
$SQL = "SELECT distinct manufacturers_id,
				manufacturers_name
		FROM manufacturers, stockmaster, stockcategory
		where stockmaster.brand = manufacturers.manufacturers_id
		AND stockmaster.categoryid = stockcategory.categoryid
		AND stockcategory.categorydescription = "."'".$_GET['StockCat']."'"."
		ORDER BY manufacturers_name";
}
		$result2 = DB_query($SQL, $db);

}

else
{
	$SQL = "select * from manufacturers";
$result2 = DB_query($SQL, $db);
}
echo '<p class="page_title_text">
         <img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
      </p>';

echo '<table class="selection">
		<tr>
			<td>' . _('From Stock Location') . ':</td>
			<td><select name="StockLocation" onchange=" ReloadForm(searchform.UpdateCategories)"> ';
			if (!isset($_GET['StockLocation'])){
	$_GET['StockLocation']='';
}
if ($_GET['StockLocation']==''){
	echo '<option selected="selected" value="">' . _('All') . '</option>';
} else {
	echo '<option value="">' . _('All') . '</option>';
}
		
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_GET['StockLocation']) AND $_GET['StockLocation']!=""){
		if ($myrow['loccode'] == $_GET['StockLocation']){
		     echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
		
	}
	elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		 $_GET['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
}
echo '</select></td>
	</tr>';
echo'			<tr>
			<td>' . _('From Sub Store') . ':</td>
			<td><select name="substore" onchange=" ReloadForm(searchform.UpdateCategories)"> ';
			if (!isset($_GET['substore'])){
	$_GET['substore']='';
}
if ($_GET['substore']==''){
	echo '<option selected="selected" value="">' . _('All') . '</option>';
} else {
	echo '<option value="">' . _('All') . '</option>';
}
		
while ($myrow=DB_fetch_array($resultSubstores)){
	
	if (isset($_SESSION['substore']) AND $_SESSION['substore']!=""){
		if ($myrow['substoreid'] == $_SESSION['substore']){
		     echo '<option selected="selected" value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
		}
	} 
	 elseif ($myrow['substoreid']==$_SESSION['substore']){
		 echo '<option selected="selected" value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
		 $_SESSION['substore']=$myrow['substoreid'];
	} else {
		 echo '<option value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
	}
	
	
}
echo '</select></td>
	</tr>';
echo'			<tr>
			<td>' . _('User') . ':</td>
			<td><select name="user" onchange=" ReloadForm(searchform.UpdateCategories)"> ';
			if (!isset($_GET['user'])){
	$_GET['user']='';
}
if ($_GET['user']==''){
	echo '<option selected="selected" value="">' . _('All') . '</option>';
} else {
	echo '<option value="">' . _('All') . '</option>';
}
		
while ($myrow=DB_fetch_array($resultUsers)){
	
	if (isset($_GET['user']) AND $_GET['user']!=""){
		if ($myrow['realname'] == $_GET['user']){
		     echo '<option selected="selected" value="' . $myrow['realname'] . '">' . $myrow['realname'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['realname'] . '">' . $myrow['realname'] . '</option>';
		}
	} 
	 elseif ($myrow['realname']==$_SESSION['user']){
		 echo '<option selected="selected" value="' . $myrow['realname'] . '">' . $myrow['realname'] . '</option>';
		 $_GET['user']=$myrow['realname'];
	} else {
		 echo '<option value="' . $myrow['realname'] . '">' . $myrow['realname'] . '</option>';
	}
	
	
}
echo '</select>';
echo "withheld stock <select name = \"period\">
<option value = 'all'> All </option>

<option value = 'week'> For more than a week</option>

<option value = 'month'> For more than a month</option>



</select>";	
/*if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
   $_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
   $_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')-1,Date('d'),Date('y')));
}
echo ' ' . _('Show Movements before') . ': <input type="text" name="BeforeDate" size="12" maxlength="12" value="' . $_POST['BeforeDate'] . '" />';
echo ' ' . _('But after') . ': <input type="text" name="AfterDate" size="12" maxlength="12" value="' . $_POST['AfterDate'] . '" />';
*/
echo '</td>
     </tr>';

if(isset($_GET['UpdateBrands']))
{

if ($_GET['brand'] == '')
{
	$SQL="SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$result1 = DB_query($SQL,$db);
}
else
{
	$SQL="SELECT distinct stockcategory.categoryid,
				stockcategory.categorydescription
		FROM  manufacturers, stockmaster, stockcategory
		where stockmaster.brand = manufacturers.manufacturers_id
		and stockmaster.categoryid = stockcategory.categoryid
		and manufacturers.manufacturers_id = "."'".$_GET['brand']."'"."
		
		ORDER BY categorydescription";

}
		$result1 = DB_query($SQL, $db);

}

else
{
	$SQL="SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$result1 = DB_query($SQL,$db);
}




if (DB_num_rows($result1)==0){
	echo '</table><p>';
	prnMsg(_('There are no stock categories currently defined please use the link below to set them up'),'warn');
	echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
	include ('includes/footer.inc');
	exit;
}

echo '<tr><td>' . _('In Stock Category') . ':</td>
		<td><select name="StockCat" onchange="ReloadForm(searchform.UpdateCategories)">';
if (!isset($_GET['StockCat'])){
	$_GET['StockCat']='';
}
if ($_GET['StockCat']==''){
	echo '<option selected="selected" value="">' . _('All') . '</option>';
} else {
	echo '<option value="">' . _('All') . '</option>';
}
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['categorydescription']==$_GET['StockCat']){
		echo '<option selected="selected" value="' . $myrow1['categorydescription'] . '">' . $myrow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow1['categorydescription'] . '">' . $myrow1['categorydescription'] . '</option>';
	}
}

echo '</select></td></tr>';
echo '<tr><td>' . _('Shown Only Items Where') . ':</td>
		<td><select name="BelowReorderQuantity">';
if (!isset($_GET['BelowReorderQuantity'])){
	$_GET['BelowReorderQuantity']='All';
}
if ($_GET['BelowReorderQuantity']=='All'){
	echo '<option selected="selected" value="All">' . _('All') . '</option>
          <option value="Below">' . _('Only items below re-order quantity') . '</option>
          <option value="NotZero">' . _('Only items where stock is available') . '</option>
          <option value="OnOrder">' . _('Only items currently on order') . '</option>';
} else if ($_GET['BelowReorderQuantity']=='Below') {
	echo '<option value="All">' . _('All') . '</option>
          <option selected="selected" value="Below">' . _('Only items below re-order quantity') . '</option>
          <option value="NotZero">' . _('Only items where stock is available') . '</option>
          <option value="OnOrder">' . _('Only items currently on order') . '</option>';
} else if ($_GET['BelowReorderQuantity']=='OnOrder') {
    echo '<option value="All">' . _('All') . '</option>
          <option value="Below">' . _('Only items below re-order quantity') . '</option>
          <option value="NotZero">' . _('Only items where stock is available') . '</option>
          <option selected="selected" value="OnOrder">' . _('Only items currently on order') . '</option>';
} else  {
	echo '<option value="All">' . _('All') . '</option>
          <option value="Below">' . _('Only items below re-order quantity') . '</option>
          <option selected="selected" value="NotZero">' . _('Only items where stock is available') . '</option>
          <option value="OnOrder">' . _('Only items currently on order') . '</option>';
}

echo '</select></td></tr>
<tr>';
echo '<td>' . _('Brand') . ':';
echo '</td><td>';
echo '<select name="brand" onchange="ReloadForm(searchform.UpdateBrands)">';
if (!isset($_SESSION['brand'])) {
	$_SESSION['brand'] ='';
}

if ($_SESSION['brand'] == '') {
	echo '<option selected="selected" value="">' . _('All') . '</option>';
} else {
	echo '<option value="">' . _('All') . '</option>';
}

while ($myrow2 = DB_fetch_array($result2)) {
	if ($myrow2['manufacturers_id'] == $_SESSION['brand']) {
		echo '<option selected="selected" value="' . $myrow2['manufacturers_id'] . '">' . $myrow2['manufacturers_name'] . '</option>';
	} else {
		echo '<option value="' . $myrow2['manufacturers_id'] . '">' . $myrow2['manufacturers_name'] . '</option>';
	}
}
echo '</select></td>';



echo '</tr>
     </table>';
echo '<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />';
echo '<input type="submit" name="UpdateBrands" style="visibility:hidden;width:1px" value="' . _('Brands') . '" />';

echo '<br />
     <div class="centre">
	 
          <input type="submit" name="ShowStatus" value="' . _('Show Stock Status') . '" />
		  <input type="submit" name="reset" value="' . _('reset') . '" />
     </div>
	      <div class="centre">
	 
          <p><a href="'.$RootPath.'/PDFstocklist.php?Stoc=' . $RequestNo . '">' .  _('Print the Stock List'). '</a></p>
	
     </div>;
	 ';

if (isset($_GET['ShowStatus']) ||isset($_GET['PageOffset']) || isset($_GET['updatereorderlevel']) ){

	if ($_GET['StockLocation']!='' ) {
		
	$sql = "SELECT distinct locstock.stockid,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						manufacturers.manufacturers_name,
						locstock.loccode,
						locstock.bin,
						locations.locationname,
						locstock.quantity,
						locstock.reorderlevel,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled
					FROM locstock,
						stockmaster,
						locations,
						manufacturers,
						stockcategory
					WHERE locstock.stockid=stockmaster.stockid
						AND locstock.loccode = '" . $_GET['StockLocation'] . "'
						AND locstock.loccode=locations.loccode
							AND stockmaster.brand = manufacturers.manufacturers_id
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
							
								AND stockmaster.brand like '%" . $_SESSION['brand'] . "%'
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockcategory.categorydescription like "."'%".$_GET['StockCat']."%'"."
		
					ORDER BY stockcategory.categorydescription, stockmaster.mnfpno";
	

		if ($_GET['substore'] != '')
		{
		$sql = "SELECT distinct substorestock.stockid,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						manufacturers.manufacturers_name,
						substorestock.quantity as quantity,
						
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled
					FROM substorestock,
						stockmaster,
						locations,
						manufacturers,
						stockcategory
					WHERE substorestock.stockid=stockmaster.stockid
							AND stockmaster.brand = manufacturers.manufacturers_id
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						AND substorestock.substoreid = " . $_GET['substore'] . "
						
						
					AND stockmaster.brand like '%" . $_SESSION['brand'] . "%'
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockcategory.categorydescription like "."'%".$_GET['StockCat']."%'"."
		
					ORDER BY stockcategory.categorydescription, stockmaster.mnfpno
					";
	
		}
	
	if(isset($_SESSION['user']) and $_SESSION['user'] != '')  {
			
					$sqlogp = "SELECT MAX(despatchdate) as maxdate FROM posdispatch inner join `posdispatchitems` on posdispatch.`dispatchid` = posdispatchitems.`dispatchid`
					WHERE `stockid` = '" . mb_strtoupper($myrow['stockid']). "' and deliveredto = '" . $_SESSION['user'] . "' ";
					$resultogp = DB_query($sqlogp,$db);
					$rowogp = DB_fetch_array($resultogp);
		
	
	$sql = "SELECT distinct stockissuance.stockid,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						manufacturers.manufacturers_name,
						stockissuance.issued as issued,
						stockissuance.returned as returned,
						stockissuance.dc as dc,
						
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled
					FROM stockissuance,
						stockmaster,
						locations,
						manufacturers,
						stockcategory
					WHERE stockissuance.stockid=stockmaster.stockid
							AND stockmaster.brand = manufacturers.manufacturers_id
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						AND stockissuance.salesperson='" . $_SESSION['user'] . "'
							
							
					AND stockmaster.brand like '%" . $_GET['brand'] . "%'
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockcategory.categorydescription like "."'%".$_GET['StockCat']."%'"."
		
					
					ORDER BY stockissuance.issued desc";
	
	
	}
	$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	}

	
	


	$ErrMsg =  _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');
	$query_limit_serial = sprintf("%s LIMIT %d, %d", $query_serial, $startRow_serial, $maxRows_serial);

$LocStockResultLimit = sprintf("%s LIMIT %d, %d", $sql, ($_GET['PageOffset'] - 1)*50, 50 );
	$LocStockResultLimitResult = DB_query($LocStockResultLimit, $db, $ErrMsg, $DbgMsg);
	
	echo '<br />';
	$ListCount = DB_num_rows($LocStockResult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / 50) ;
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
			
				<br />
				</div>';
		}
	
	     echo '<table cellpadding="5" cellspacing="4" class="selection">';

	if (isset($_SESSION['user']) and $_SESSION['user']!='')
	{
	$TableHeader = '<tr>
						
    					<th>' . _('StockID') . '</th>
						<th>' . _('Manufacturer Code') . '</th>
						<th>' . _('Part Number') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Brand') . '</th>
    					<th>' . _('Quantity On Hand') . '</th>
    					<th>' . _('Returned') . '</th>
    					<th>' . _('Items in DCs') . '</th>
    					<th>' . _('Total') . '</th>
    					<th>' . _('Last OGP') . '</th>
    					<th>' . _('Last IGP') . '</th>
						<th>' . _('Last DC') . '</th>
					
					</tr>';
	}
	else
	{
		$TableHeader = '<tr>
						<th>' . _('Category') . '</th>
    					<th>' . _('StockID') . '</th>
						<th>' . _('Manufacturer Code') . '</th>
						<th>' . _('Part Number') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Brand') . '</th>
    					<th>' . _('Quantity On Hand') . '</th>
    					<th>' . _('Bin Loc') . '</th>
    					<th>' . _('Re-Order Level') . '</th>
    					<th>' . _('Demand') . '</th>
    					<th>' . _('Available') . '</th>
    					<th>' . _('On Order') . '</th>
					</tr>';
	
	}
	echo $TableHeader;
	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($LocStockResultLimitResult)) {

		$StockID = $myrow['stockid'];

		$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
                   	FROM salesorderdetails INNER JOIN salesorders
                   	ON salesorders.orderno = salesorderdetails.orderno
        			WHERE salesorders.fromstkloc='" . $myrow['loccode'] . "'
        			AND salesorderdetails.completed=0
        			AND salesorderdetails.stkcode='" . $StockID . "'
        			AND salesorders.quotation=0";

		$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($sql,$db,$ErrMsg);

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
		$DemandResult = DB_query($sql,$db, $ErrMsg);

		if (DB_num_rows($DemandResult)==1){
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty += $DemandRow[0];
		}
		$sql = "SELECT SUM((woitems.qtyreqd-woitems.qtyrecd)*bom.quantity) AS dem
				FROM workorders INNER JOIN woitems
                     ON woitems.wo = workorders.wo
                INNER JOIN bom
                      ON woitems.stockid =  bom.parent
				WHERE workorders.closed=0
				AND   bom.component = '". $StockID . "'
				AND   workorders.loccode='". $myrow['loccode'] ."'";
		$DemandResult = DB_query($sql,$db, $ErrMsg);

		if (DB_num_rows($DemandResult)==1){
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty += $DemandRow[0];
		}


		$sql = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS qoo
				FROM purchorderdetails
				INNER JOIN purchorders
					ON purchorderdetails.orderno=purchorders.orderno
				WHERE purchorders.intostocklocation='" . $myrow['loccode'] . "'
				AND purchorderdetails.itemcode='" . $StockID . "'
				AND (purchorders.status = 'Authorised' OR purchorders.status='Printed')";

		$ErrMsg = _('The quantity on order for this product to be received into') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO =  $QOORow[0];
		} else {
			$QOO = 0;
		}

		if (($_GET['BelowReorderQuantity']=='Below' AND ($myrow['quantity']-$myrow['reorderlevel']-$DemandQty)<0)
				OR $_GET['BelowReorderQuantity']=='All' OR $_GET['BelowReorderQuantity']=='NotZero'
                OR ($_GET['BelowReorderQuantity']=='OnOrder' AND $QOO != 0)){

			if (($_GET['BelowReorderQuantity']=='NotZero') AND (($myrow['quantity']-$DemandQty)>0)) {

				if ($k==1){
					echo '<tr class="OddTableRows">';
					$k=0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k=1;
				}
				
				
				$sql = "select stockcategory.categorydescription 
				from stockmaster,stockcategory where stockcategory.categoryid = stockmaster.categoryid and
				stockmaster.stockid = '".mb_strtoupper($myrow['stockid'])."'
				";
				$Result = DB_query($sql,$db);
				$myr = DB_fetch_array($Result);
				echo '<td>'.$myr['categorydescription'].'</td>';
				echo '<input type = "hidden" name = "stockid[]" value = "'.mb_strtoupper($myrow['stockid']).'">';
				
				printf('<td><a target="_blank" href="' . $RootPath . '/SelectProduct.php?Select=%s">%s</a></td>
    					<td>%s</td>
    					<td class="number">%s</td>
    					<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
    					<td><input type = "number" name = "reorderlevel[]" value = "%s"></td>
    					<td class="number">%s</td>
    					<td class="number"><a target="_blank" href="' . $RootPath . '/SelectProduct.php?StockID=%s">%s</a></td>
    					<td class="number">%s</td>
    					</tr>',
    					mb_strtoupper($myrow['stockid']),
    					mb_strtoupper($myrow['stockid']),
						$myrow['mnfCode'],
						$myrow['mnfpno'],
    					$myrow['description'],
						$myrow['manufacturers_name'],
    					locale_number_format($myrow['quantity'],$myrow['decimalplaces']),
    					$myrow['bin'],
    					locale_number_format($myrow['reorderlevel'],$myrow['decimalplaces']),
    					locale_number_format($DemandQty,$myrow['decimalplaces']),
    					mb_strtoupper($myrow['stockid']),
    					locale_number_format($myrow['quantity'] - $DemandQty,$myrow['decimalplaces']),
    					locale_number_format($QOO,$myrow['decimalplaces']));
				
				
			if (isset($_SESSION['user']) and $_SESSION['user']!='')
			{	
			$sqldc = "SELECT MAX(despatchdate) as maxdate FROM dc inner join `dcitems` on dc.`dispatchid` = dcitems.`dispatchid`
					WHERE `stockid` = '" . $myrow['stockid'] . "' and salesperson = '" . $_SESSION['user'] . "' ";
					$resultdc = DB_query($sqldc,$db);
					$rowdc = DB_fetch_array($resultdc);
					
			printf('<td><a target="_blank" href="' . $RootPath . '/SelectProduct.php?Select=%s">%s</a></td>
    					<td>%s</td>
    					<td class="number">%s</td>
    					<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
    					<td>%s</td>
    					<td class="number">%s</td>
    					<td class="number"><a target="_blank" href="' . $RootPath . '/SelectProduct.php?StockID=%s">%s</a></td>
    					<td>%s</td>
    					</tr>',
    					mb_strtoupper($myrow['stockid']),
    					mb_strtoupper($myrow['stockid']),
						$myrow['mnfCode'],
						$myrow['mnfpno'],
    					$myrow['description'],
						$myrow['manufacturers_name'],
    					locale_number_format($myrow['issued'],$myrow['decimalplaces']),
						locale_number_format($myrow['returned'],$myrow['decimalplaces']),
						locale_number_format($myrow['dc'],$myrow['decimalplaces']),
    					locale_number_format($myrow['dc']+$myrow['returned']+$myrow['issued'],$myrow['decimalplaces']),
    					
						$rowdc['maxdate'],
    					mb_strtoupper($myrow['stockid']),
    					locale_number_format($myrow['quantity'] - $DemandQty,$myrow['decimalplaces']),
    					locale_number_format($QOO,$myrow['decimalplaces']));
			}
				if ($myrow['serialised'] ==1){ /*The line is a serialised item*/

					echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Serialised=Yes&Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Serial Numbers') . '</a></td></tr>';
				} elseif ($myrow['controlled']==1){
					echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Batches') . '</a></td></tr>';
				}
			} else if ($_GET['BelowReorderQuantity']!='NotZero') {
				if ($k==1){
					echo '<tr class="OddTableRows">';
					$k=0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k=1;
				}
				
						if (isset($_SESSION['user']) and $_SESSION['user']!='')
			{
				
		
			$sqldc = "SELECT MAX(despatchdate) as maxdate FROM dc inner join `dcitems` on dc.`dispatchid` = dcitems.`dispatchid`
					WHERE `stockid` = '" . mb_strtoupper($myrow['stockid']). "' and salesperson = '" . $_SESSION['user'] . "' ";
					$resultdc = DB_query($sqldc,$db);
					$rowdc = DB_fetch_array($resultdc);
					$sqligp = "SELECT MAX(despatchdate) as maxdate FROM igp inner join `igpitems` on igp.`dispatchid` = igpitems.`dispatchid`
					WHERE `stockid` = '" . mb_strtoupper($myrow['stockid']). "' and receivedfrom = '" . $_SESSION['user'] . "' ";
					$resultigp = DB_query($sqligp,$db);
					$rowigp = DB_fetch_array($resultigp);
					$sqlogp = "SELECT MAX(despatchdate) as maxdate FROM posdispatch inner join `posdispatchitems` on posdispatch.`dispatchid` = posdispatchitems.`dispatchid`
					WHERE `stockid` = '" . mb_strtoupper($myrow['stockid']). "' and deliveredto = '" . $_SESSION['user'] . "' ";
					$resultogp = DB_query($sqlogp,$db);
					$rowogp = DB_fetch_array($resultogp);
		
		if ($_GET['period'] == 'all')
		{
		
			printf('<td><a target="_blank" href="' . $RootPath . '/SelectProduct.php?Select=%s">%s</a></td>
    					<td>%s</td>
    					<td class="number">%s</td>
    					<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
    					<td class="number">%s</td>
    					<td class="number">%s</td>
    					<td>%s</td>
						<td>%s</td>
						<td>%s</td>
    					</tr>',
    					mb_strtoupper($myrow['stockid']),
    					mb_strtoupper($myrow['stockid']),
						$myrow['mnfCode'],
						$myrow['mnfpno'],
    					$myrow['description'],
						$myrow['manufacturers_name'],
    					locale_number_format($myrow['issued'],$myrow['decimalplaces']),
						locale_number_format($myrow['returned'],$myrow['decimalplaces']),
						locale_number_format($myrow['dc'],$myrow['decimalplaces']),
    					locale_number_format($myrow['dc']+$myrow['returned']+$myrow['issued'],$myrow['decimalplaces']),
    					
						$rowogp['maxdate'],
						$rowigp['maxdate'],
						$rowdc['maxdate']
    					
    					);
			
		}
		else if ($_GET['period'] == 'week')
		{
			$dat = date("Y-m-d H:i:s");
			if ($rowogp['maxdate'] < mktime(0, 0, 0, date("m"), date("d") - 7,   date("Y")) and $myrow['issued'] > 0)
			{
				
				printf('<td><a target="_blank" href="' . $RootPath . '/SelectProduct.php?Select=%s">%s</a></td>
    					<td>%s</td>
    					<td class="number">%s</td>
    					<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
    					<td class="number">%s</td>
    					<td class="number">%s</td>
    					<td>%s</td>
						<td>%s</td>
						<td>%s</td>
    					</tr>',
    					mb_strtoupper($myrow['stockid']),
    					mb_strtoupper($myrow['stockid']),
						$myrow['mnfCode'],
						$myrow['mnfpno'],
    					$myrow['description'],
						$myrow['manufacturers_name'],
    					locale_number_format($myrow['issued'],$myrow['decimalplaces']),
						locale_number_format($myrow['returned'],$myrow['decimalplaces']),
						locale_number_format($myrow['dc'],$myrow['decimalplaces']),
    					locale_number_format($myrow['dc']+$myrow['returned']+$myrow['issued'],$myrow['decimalplaces']),
    					
						$rowogp['maxdate'],
						$rowigp['maxdate'],
						$rowdc['maxdate']
    					
    					);
				
			}
		}
		else	if ($_GET['period'] == 'month')
		{				
			if ($rowogp['maxdate'] < mktime(0, 0, 0, date("m"), date("d") - 7 )and $myrow['issued'] > 0)
			{
				
				printf('<td><a target="_blank" href="' . $RootPath . '/SelectProduct.php?Select=%s">%s</a></td>
    					<td>%s</td>
    					<td class="number">%s</td>
    					<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
    					<td class="number">%s</td>
    					<td class="number">%s</td>
    					<td>%s</td>
						<td>%s</td>
						<td>%s</td>
    					</tr>',
    					mb_strtoupper($myrow['stockid']),
    					mb_strtoupper($myrow['stockid']),
						$myrow['mnfCode'],
						$myrow['mnfpno'],
    					$myrow['description'],
						$myrow['manufacturers_name'],
    					locale_number_format($myrow['issued'],$myrow['decimalplaces']),
						locale_number_format($myrow['returned'],$myrow['decimalplaces']),
						locale_number_format($myrow['dc'],$myrow['decimalplaces']),
    					locale_number_format($myrow['dc']+$myrow['returned']+$myrow['issued'],$myrow['decimalplaces']),
    					
						$rowogp['maxdate'],
						$rowigp['maxdate'],
						$rowdc['maxdate']
    					
    					);
				
			}
		}
			}
			else
			{
				
				$sql = "select stockcategory.categorydescription 
				from stockmaster,stockcategory where stockcategory.categoryid = stockmaster.categoryid and
				stockmaster.stockid = '".mb_strtoupper($myrow['stockid'])."'
				";
				$Result = DB_query($sql,$db);
				$myr = DB_fetch_array($Result);
				echo '<td>'.$myr['categorydescription'].'</td>';
				echo '<input type = "hidden" name = "stockid[]" value = "'.mb_strtoupper($myrow['stockid']).'">';
				
				printf('<td><a target="_blank" href="' . $RootPath . '/SelectProduct.php?Select=%s">%s</a></td>
    					<td>%s</td>
    					<td class="number">%s</td>
    					<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
    					<td><input type = "number" name = "reorderlevel[]" value = "%s"></td>
    					<td class="number">%s</td>
    					<td class="number"><a target="_blank" href="' . $RootPath . '/SelectProduct.php?StockID=%s">%s</a></td>
    					<td class="number">%s</td>
    					</tr>',
    					mb_strtoupper($myrow['stockid']),
    					mb_strtoupper($myrow['stockid']),
						$myrow['mnfCode'],
						$myrow['mnfpno'],
    					$myrow['description'],
						$myrow['manufacturers_name'],
    					locale_number_format($myrow['quantity'],$myrow['decimalplaces']),
    					$myrow['bin'],
    					locale_number_format($myrow['reorderlevel'],$myrow['decimalplaces']),
    					locale_number_format($DemandQty,$myrow['decimalplaces']),
    					mb_strtoupper($myrow['stockid']),
    					locale_number_format($myrow['quantity'] - $DemandQty,$myrow['decimalplaces']),
    					locale_number_format($QOO,$myrow['decimalplaces']));
				
				
			}
			
				if ($myrow['serialised'] ==1){ /*The line is a serialised item*/

					echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Serialised=Yes&Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Serial Numbers') . '</a></td></tr>';
				} elseif ($myrow['controlled']==1){
					echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Batches') . '</a></td></tr>';
				}
			} //end of page full new headings if
		} //end of if BelowOrderQuantity or all items
	}
	//end of while loop

	echo '</table>
	<input type = "submit" name = "updatereorderlevel" value = "Update Reorder Levels">
	
	';
} /* Show status button hit */
echo '</div>
      </form>';
	 
}
	
	  
include('includes/footer.inc');

?>