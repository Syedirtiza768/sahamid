<?php

/* $Id: InternalStockRequestFulfill.php  $*/

include('includes/session.inc');

$Title = _('Fulfill Stock Requests');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Contract') . '" alt="" />' . _('Fulfill Stock Requests') . '</p>';
 $sql="SELECT stockrequest.dispatchid,
			locations.locationname,
			stockrequest.dispatchdate,
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
		AND stockrequest.salesperson = '".$_SESSION['UsersRealName']."'
	";
	$result=DB_query($sql, $db);

	if (DB_num_rows($result)==0) {
		prnMsg( _('There are no outstanding authorised requests for this location'), 'info');
		echo '<br />';
		echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Select another location') . '</a></div>';
		include('includes/footer.inc');
		exit;
	}

	echo '<form method="POST" action="StockLocTransferRequested.php">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	
	echo '<table class="selection">
			<tr>
			
				<th>' . _('Request Number') . '</th>
				<th>' . _('Request by') . '</th>
				<th>' . _('Location Of Stock') . '</th>
				<th>' . _('Requested Date') . '</th>
				<th>' . _('Narrative') . '</th>
			</tr>';
$maxdispatchitemid = array();
	while ($myrow=DB_fetch_array($result)) {
$slctdispatchid = $myrow['dispatchid'];

		echo '<tr>
				<td>' . $myrow['dispatchid'] . '</td>
				<td>' . $myrow['authorizer'] . '(' . $myrow['recloc'] . ')</td>
				<td>' . $myrow['locationname'] . '</td>
				<td>' . ConvertSQLDate($myrow['dispatchdate']) . '</td>
				<td>' . $myrow['narrative'] . '</td>
			<td>	<input type="hidden" name="authorizer' . $myrow['dispatchid'] . '" value="' . $myrow['authorizer'] . '" /></td>
			<td>	<input type="hidden" name="fromloc' . $myrow['dispatchid'] . '" value="' . $myrow['shiploc'] . '" /></td>
			<td>	<input type="hidden" name="toloc' . $myrow['dispatchid'] . '" value="' . $myrow['recloc'] . '" /></td>
			
			</tr>';
			
	
		$LineSQL="SELECT stockrequestitems.dispatchitemsid,
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
			WHERE dispatchid='".$myrow['dispatchid'] . "'
				AND completed=0
				
				
				";
		$LineResult=DB_query($LineSQL, $db);


		while ($LineRow=DB_fetch_array($LineResult)) {
			
		echo '<tr>
				<td></td>
				<td colspan="5" align="left">
					<table class="selection" align="left">
					<tr>
						<th>' . _('Model No.') . '</th>
						<th>' . _('Quantity') . '<br />' . _('Required') . '</th>
						<th>' . _('Quantity') . '<br />' . _('Delivered') . '</th>
							<th>' . _('Comments') . '</th>
					

						
					</tr>';
			echo '<tr>
					<td>' . $LineRow['mnfCode'] . '</td>
					<td class="number">' . locale_number_format($LineRow['quantity']-$LineRow['qtydelivered'],$LineRow['decimalplaces']) . '</td>
					<td class="number"><input type="number" readonly = "readonly" required = "required" class="number" name="'. $LineRow['dispatchid'] . 'Qty'. $LineRow['dispatchitemsid'] . '" value= 0 /></td>
					<td>' . $LineRow['comments'] . '</td>
					
					<td>' . $LineRow['uom'] . '</td>
			';
			$maxdispatchitemid[$slctdispatchid] = locale_number_format($LineRow['dispatchitemsid'],$LineRow['decimalplaces']);
			
			echo '<input type="hidden" class="number" name="'. $LineRow['dispatchid'] . 'StockID'. $LineRow['dispatchitemsid'] . '" value="'.$LineRow['stockid'].'" />';
			echo '<input type="hidden" class="number" name="'. $LineRow['dispatchid'] . 'RequestedQuantity'. $LineRow['dispatchitemsid'] . '" value="'.locale_number_format($LineRow['quantity']-$LineRow['qtydelivered'],$LineRow['decimalplaces']).'" />';
			echo '<input type="hidden" class="number" name="'. $LineRow['dispatchid'] . 'Department'. $LineRow['dispatchitemsid'] . '" value="'.$myrow['description'].'" />';
	
//end of page full new headings if
echo '</table>';

		} // end while order line detail
		echo '</td></tr>';
		
		
		echo '<input type="hidden" class="number" name="'.$slctdispatchid .'maxdispatchitemid" value="'.locale_number_format($maxdispatchitemid[$slctdispatchid],$LineRow['decimalplaces']).'" />';

	} //end while header loop
	//$maxdispatchitemid = locale_number_format($LineRow['dispatchitemsid'],$LineRow['decimalplaces']);
		
		$maxdispatchid  = locale_number_format($_POST['slct'],$LineRow['decimalplaces']);
			
	echo '<input type="hidden" class="number" name="maxdispatchid" value="'.locale_number_format($maxdispatchid,$LineRow['decimalplaces']).'" />';
	
	//	echo '<input type="hidden" class="number" name="maxdispatchitemid" value="'.locale_number_format($maxdispatchitemid,$LineRow['decimalplaces']).'" />';
					
	echo '</table>';
	echo '<br /><div class="centre"><input type="submit" name="UpdateAll" value="' . _('Update'). '" /></div>
          </div>
          </form>';


include('includes/footer.inc');

?>
