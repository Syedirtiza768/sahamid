<?php

/* $Id: StockLocStatus.php 6033 2013-06-24 07:36:26Z daintree $*/

include('includes/session.inc');

$Title = _('All Stock Status By Location/Category');

include('includes/header.inc');


			
					$sqlogp = "SELECT MAX(despatchdate) as maxdate FROM posdispatch inner join `posdispatchitems` on posdispatch.`dispatchid` = posdispatchitems.`dispatchid`
					WHERE `stockid` = '" . mb_strtoupper($myrow['stockid']). "' and deliveredto = '" . $_SESSION['UsersRealName'] . "' ";
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
						AND stockissuance.salesperson='" . $_SESSION['UsersRealName'] . "'
							
							
					AND stockmaster.brand like '%" . $_GET['brand'] . "%'
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockissuance.issued>0
					AND stockcategory.categorydescription like "."'%".$_GET['StockCat']."%'"."
		
					
					ORDER BY stockissuance.issued desc";
	
	

	$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	

	
	     echo '<table cellpadding="5" cellspacing="4" class="selection">';

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
	echo $TableHeader;
	$j = 1;
	$k=0; //row colour counter
while ($myrow=DB_fetch_array($LocStockResult)) {
		
		
			$sqldc = "SELECT MAX(despatchdate) as maxdate FROM dc inner join `dcitems` on dc.`dispatchid` = dcitems.`dispatchid`
					WHERE `stockid` = '" . mb_strtoupper($myrow['stockid']). "' and salesperson = '" .  $_SESSION['UsersRealName']. "' ";
					$resultdc = DB_query($sqldc,$db);
					$rowdc = DB_fetch_array($resultdc);
					$sqligp = "SELECT MAX(despatchdate) as maxdate FROM igp inner join `igpitems` on igp.`dispatchid` = igpitems.`dispatchid`
					WHERE `stockid` = '" . mb_strtoupper($myrow['stockid']). "' and receivedfrom = '" . $_SESSION['UsersRealName']. "' ";
					$resultigp = DB_query($sqligp,$db);
					$rowigp = DB_fetch_array($resultigp);
					$sqlogp = "SELECT MAX(despatchdate) as maxdate FROM posdispatch inner join `posdispatchitems` on posdispatch.`dispatchid` = posdispatchitems.`dispatchid`
					WHERE `stockid` = '" . mb_strtoupper($myrow['stockid']). "' and deliveredto = '" . $_SESSION['UsersRealName'] . "' ";
					$resultogp = DB_query($sqlogp,$db);
					$rowogp = DB_fetch_array($resultogp);
		
		
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
			
	
	echo '</table>
	
	
	';
 /* Show status button hit */
echo '</div>
      </form>';
	 

	
	  
include('includes/footer.inc');

?>