<?php

/* $Id: DiscountCategories.php 6484 2013-12-09 02:00:55Z exsonqu $*/

include('includes/session.inc');

$Title = _('Price Factor Maintenance');
/* webERP manual links before header.inc */
$ViewTopic= "SalesOrders";
$BookMark = "DiscountMatrix";
include('includes/header.inc');
if (isset($_GET['brand']))
$_SESSION['brand'] = $_GET['brand'];
if (isset($_GET['StockCat']))
$_SESSION['StockCat'] = $_GET['StockCat'];

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';
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

echo '</select></td></tr>
<tr><td>By Sea</td><td><input name = "sea"></td></tr>
<tr><td>By Land</td><td><input name = "land"></td></tr>
<tr><td>Local</td><td><input name = "local"></td></tr>

</table>
<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />
	
<div class="centre"><input type="submit" name="Submit" value="' . _('Submit') . '" /></div><br />

</form>';
if (isset($_GET['Submit']))
{
	$sql = "select brandid,
			categorycode,
			sea,
			land,
			local 
			from pricefactor
			where brandid = '".$_GET['brand']."' and categorycode  = '".$_GET['StockCat']."'";
	$result = DB_query($sql, $db);
	
	echo DB_num_rows($result);
if (DB_num_rows($result) == 0)
{
echo	$sql = "insert into pricefactor(brandid,
									categorycode) 
									values ('".$_GET['brand']."',
									'".$_GET['StockCat']."')";
}
	
	
}
include('includes/footer.inc');
?>
