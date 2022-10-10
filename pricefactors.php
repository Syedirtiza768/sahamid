<?php

/* $Id: DiscountCategories.php 6484 2013-12-09 02:00:55Z exsonqu $*/

include('includes/session.inc');

$Title = _('Price Factor Maintenance');
/* webERP manual links before header.inc */
$ViewTopic= "SalesOrders";
$BookMark = "DiscountMatrix";
include('includes/header.inc');


echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';
if (isset($_GET['brand']))
echo $_SESSION['brand'] = $_GET['brand'];
if (isset($_GET['StockCat']))
$_SESSION['StockCat'] = $_GET['StockCat'];
if(isset($_GET['UpdateCategories']))
{
	 $_SESSION['brand'] = $_GET['brand'];
$_SESSION['StockCat'] = $_GET['StockCat'];

	
}


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
echo '<form name = "searchform" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="GET">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Maintain Price Factors'). '</p>';
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
	if ($myrow1['categoryid'] == $_SESSION['StockCat']) {
		echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	}
}
echo '</select></td>';
echo '<td>' . _('Brand') . ':';

echo '<select name="brand" onchange="ReloadForm(searchform.UpdateCategories)">';



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
		echo '<option selected="selected" value="' . $myrow2['manufacturers_id'] . '">' . $myrow2['manufacturers_name'] . '</option>';
	} else {
		echo '<option value="' . $myrow2['manufacturers_id'] . '">' . $myrow2['manufacturers_name'] . '</option>';
	}
}


echo '</select>';
if (isset($_GET['UpdateCategories']))
{

$sql = "SELECT distinct  `bysea`, `byair`, `bylocal`,
	`showbysea`, `showbyair`, `showbylocal`
	 FROM `pricefactor`

						
									where stockid like '".$_SESSION['brand']."_".$_SESSION['StockCat']."%'
									";

	
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
}
if (isset($_GET['Submit']) and $_GET['Submit'] == 'Submit')
{

	 $sql = "update pricefactor

						set bysea = '".$_GET['bysea']."',
							byair = '".$_GET['byair']."',
							bylocal = '".$_GET['bylocal']."',
							showbyair = '".$_GET['showbyair']."',
							showbylocal = '".$_GET['showbylocal']."',
							showbysea = '".$_GET['showbysea']."',
							lastupdate = '".date("Y-m-d")."'
									where stockid like '".$_SESSION['brand']."_".$_SESSION['StockCat']."%'
									";

	
	DB_query($sql,$db);
	$sql = "SELECT distinct  `bysea`, `byair`, `bylocal`,
	`showbysea`, `showbyair`, `showbylocal`
	 FROM `pricefactor`

						
									where stockid like '".$_SESSION['brand']."_".$_SESSION['StockCat']."%'
									";

	
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
}
echo '</td></tr>
<tr><td>List Price By Sea</td><td><input name = "bysea" value = "'.$row['bysea'].'"></td>';
if($row['showbysea'] == 'yes')
echo '<td>Show</td><td><input name = "showbysea" value = "yes" type = "checkbox" checked></td>';
else
echo '<td>Show</td><td><input name = "showbysea" value = "yes" type = "checkbox"></td>';
	
echo'</tr>';
echo '<tr><td>List Price By Air</td><td><input name = "byair" value = "'.$row['byair'].'" >';
if($row['showbyair'] == 'yes')
echo '<td>Show</td><td><input name = "showbyair" value = "yes" type = "checkbox" checked></td>';
else
echo '<td>Show</td><td><input name = "showbyair" value = "yes" type = "checkbox"></td>';
echo'</tr>';

echo'<tr><td>List Price Local</td><td><input name = "bylocal" value = "'.$row['bylocal'].'">';
if($row['showbylocal'] == 'yes')
echo '<td>Show</td><td><input name = "showbylocal" value = "yes" type = "checkbox" checked></td>';
else
echo '<td>Show</td><td><input name = "showbylocal" value = "yes" type = "checkbox"></td>';
echo'</tr>';

echo '</table>
<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />
	
<div class="centre"><input type="submit" name="Submit" value="' . _('Submit') . '" /></div><br />

</form>';
unset($_SESSION['brand']);
unset($_SESSION['StockCat']);


include('includes/footer.inc');
?>
