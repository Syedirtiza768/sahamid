<?php
/* $Id: wotemplateegories.php 6518 2013-12-26 17:15:48Z rchacon $*/

include('includes/session.inc');

$Title = _('Workorder Template Maintenance');
$ViewTopic= 'Inventory';
$BookMark = 'WorkOrderMaintenece';
include('includes/header.inc');



echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Inventory Adjustment') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['Selectedwotemplate'])){
	$Selectedwotemplate = mb_strtoupper($_GET['Selectedwotemplate']);
} else if (isset($_POST['Selectedwotemplate'])){
	$Selectedwotemplate = mb_strtoupper($_POST['Selectedwotemplate']);
}

if (isset($_GET['DeleteProperty'])){

	$ErrMsg = _('Could not delete the property') . ' ' . $_GET['DeleteProperty'] . ' ' . _('because');
//	$sql = "DELETE FROM stockitemproperties WHERE stkcatpropid='" . $_GET['DeleteProperty'] . "'";
//	$result = DB_query($sql,$db,$ErrMsg);
	$sql = "DELETE FROM wotemplateproperties WHERE wotemplatepropid='" . $_GET['DeleteProperty'] . "'";
	$result = DB_query($sql,$db,$ErrMsg);
	prnMsg(_('Deleted the property') . ' ' . $_GET['DeleteProperty'],'success');
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['wotemplateID'] = mb_strtoupper($_POST['wotemplateID']);

	if (mb_strlen($_POST['wotemplateID']) > 6) {
		$InputError = 1;
		prnMsg(_('The Inventory wotemplate code must be six characters or less long'),'error');
	} elseif (mb_strlen($_POST['wotemplateID'])==0) {
		$InputError = 1;
		prnMsg(_('The Inventory wotemplate code must be at least 1 character but less than six characters long'),'error');
	} elseif (mb_strlen($_POST['wotemplateDescription']) >100 or mb_strlen($_POST['wotemplateDescription'])==0) {
		$InputError = 1;
		prnMsg(_('The wotemplate description must be hundred characters or less long and cannot be zero'),'error');
	} 
	//elseif ($_POST['StockType'] !='D' AND $_POST['StockType'] !='L' AND $_POST['StockType'] !='F' AND $_POST['StockType'] !='M') {
	//	$InputError = 1;
	//	prnMsg(_('The stock type selected must be one of') . ' "D" - ' . _('Dummy item') . ', "L" - ' . _('Labour stock item') . ', "F" - ' . _('Finished product') . ' ' . _('or') . ' "M" - ' . _('Raw Materials'),'error');
	
	for ($i=0;$i<=$_POST['PropertyCounter'];$i++){
		if (isset($_POST['PropNumeric' .$i]) and $_POST['PropNumeric' .$i] == true){
			if (!is_numeric(filter_number_format($_POST['PropMinimum' .$i]))){
				$InputError = 1;
				prnMsg(_('The minimum value is expected to be a numeric value'),'error');
			}
			if (!is_numeric(filter_number_format($_POST['PropMaximum' .$i]))){
				$InputError = 1;
				prnMsg(_('The maximum value is expected to be a numeric value'),'error');
			}
		}
	} //check the properties are sensible

	if (isset($Selectedwotemplate) AND $InputError !=1) {

		/*Selectedwotemplate could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE wotemplate SET 
									 wotemplatedescription = '" . $_POST['wotemplateDescription'] . "'
									 WHERE
									 wotemplateid = '" . $Selectedwotemplate. "'";
		$ErrMsg = _('Could not update the stock wotemplate') . $_POST['wotemplateDescription'] . _('because');
		$result = DB_query($sql,$db,$ErrMsg);

		if ($_POST['PropertyCounter']==0 and $_POST['PropLabel0']!='') {
			$_POST['PropertyCounter']=0;
		}

		for ($i=0;$i<=$_POST['PropertyCounter'];$i++){

			if (isset($_POST['PropReqSO' .$i]) and $_POST['PropReqSO' .$i] == true){
					$_POST['PropReqSO' .$i] =1;
			} else {
					$_POST['PropReqSO' .$i] =0;
			}
			if (isset($_POST['PropNumeric' .$i]) and $_POST['PropNumeric' .$i] == true){
					$_POST['PropNumeric' .$i] =1;
			} else {
					$_POST['PropNumeric' .$i] =0;
			}
			if ($_POST['PropID' .$i] =='NewProperty' AND mb_strlen($_POST['PropLabel'.$i])>0){
			$sql = "INSERT INTO wotemplateproperties (wotemplateid,
														label,
														controltype,
														defaultvalue,
														minimumvalue,
														maximumvalue,
														numericvalue)
											VALUES ('" . $Selectedwotemplate . "',
													'" . $_POST['PropLabel' . $i] . "',
													" . $_POST['PropControlType' . $i] . ",
													'" . $_POST['PropDefault' .$i] . "',
													'" . filter_number_format($_POST['PropMinimum' .$i]) . "',
													'" . filter_number_format($_POST['PropMaximum' .$i]) . "',
													'" . $_POST['PropNumeric' .$i] . "')";
				$ErrMsg = _('Could not insert a new wotemplate property for') . $_POST['PropLabel' . $i];
				$result = DB_query($sql,$db,$ErrMsg);
			} elseif ($_POST['PropID' .$i] !='NewProperty') { //we could be amending existing properties
			 $_POST['PropID' .$i];
				$sql = "UPDATE wotemplateproperties SET label ='" . $_POST['PropLabel' . $i] . "',
													  controltype = " . $_POST['PropControlType' . $i] . ",
													  defaultvalue = '"	. $_POST['PropDefault' .$i] . "',
													  minimumvalue = '" . filter_number_format($_POST['PropMinimum' .$i]) . "',
													  maximumvalue = '" . filter_number_format($_POST['PropMaximum' .$i]) . "',
													  numericvalue = '" . $_POST['PropNumeric' .$i] . "'
												WHERE wotemplatepropid =" . $_POST['PropID' .$i];
				$ErrMsg = _('Updated the stock wotemplate property for') . ' ' . $_POST['PropLabel' . $i];
				$result = DB_query($sql,$db,$ErrMsg);
			}

		} //end of loop round properties

		prnMsg(_('Updated the stock wotemplate record for') . ' ' . $_POST['wotemplateDescription'],'success');

	} elseif ($InputError !=1) {

	/*Selected wotemplate is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new stock wotemplate form */

		$sql = "INSERT INTO wotemplate (wotemplateid,
											
											wotemplatedescription
											)
										VALUES ('" . 
											$_POST['wotemplateID'] . "','" .
											
											$_POST['wotemplateDescription'] . "'
											)";
		$ErrMsg = _('Could not insert the new stock wotemplate') . $_POST['wotemplateDescription'] . _('because');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('A new stock wotemplate record has been added for') . ' ' . $_POST['wotemplateDescription'],'success');

	}
}
	//run the SQL from either of the above possibilites


	unset($_POST['wotemplateDescription']);
	


if (!isset($Selectedwotemplate)) {

/* It could still be the second time the page has been run and a record has been selected for modification - Selectedwotemplate will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of stock wotemplates will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT	wotemplateid,
					wotemplatedescription
				FROM wotemplate";
	$result = DB_query($sql,$db);

	echo '<br />
		<table class="selection">
			<tr>
				<th>' . _('Code') . '</th>
				<th>' . _('wotemplate Description') . '</th>' . '

				
			</tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf('<td>%s</td>
				<td>%s</td>
				
				
				<td><a href="%sSelectedwotemplate=%s">' . _('Edit') . '</a></td>
				<td><a href="%sSelectedwotemplate=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this stock wotemplate? Additional checks will be performed before actual deletion to ensure data integrity is not compromised.') . '\');">' . _('Delete') . '</a></td>
			</tr>',
				$myrow['wotemplateid'],
				$myrow['wotemplatedescription'],
				
				
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$myrow['wotemplateid'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$myrow['wotemplateid']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

echo '<br />';

if (isset($Selectedwotemplate)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" >' . _('Show All Work Order Templates') . '</a>';
}

echo '<form id="wotemplateForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<div>';
echo '<br />';

echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($Selectedwotemplate)) {
	//editing an existing stock wotemplate
	if (!isset($_POST['UpdateTypes'])) {
		$sql = "SELECT wotemplateid,
						
						wotemplatedescription
						
					FROM wotemplate
					WHERE wotemplateid='" . $Selectedwotemplate . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['wotemplateID'] = $myrow['wotemplateid'];
	
		$_POST['wotemplateDescription']  = $myrow['wotemplatedescription'];
		
	}
	echo '<input type="hidden" name="Selectedwotemplate" value="' . $Selectedwotemplate . '" />';
	echo '<input type="hidden" name="wotemplateID" value="' . $_POST['wotemplateID'] . '" />';
	echo '<table class="selection">
			<tr>
				<td>' . _('wotemplate Code') . ':</td>
				<td>' . $_POST['wotemplateID'] . '</td>
			</tr>';

} else { //end of if $Selectedwotemplate only do the else when a new record is being entered
	if (!isset($_POST['wotemplateID'])) {
		$_POST['wotemplateID'] = '';
	}
	echo '<table class="selection">
			<tr>
				<td>' . _('wotemplate Code') . ':</td>
				<td><input type="text" name="wotemplateID" required="required" autofocus="autofocus" data-type="no-illegal-chars" title="' . _('Enter up to six alphanumeric characters or underscore as a code for this stock wotemplate') . '" size="7" maxlength="6" value="' . $_POST['wotemplateID'] . '" /></td>
			</tr>';
}



// wotemplate Description input.
if (!isset($_POST['wotemplateDescription'])) {
	$_POST['wotemplateDescription'] = '';
}
echo '<tr><td><label for="wotemplateDescription">' . _('wotemplate Description') .
	':</label></td><td><input id="wotemplateDescription" maxlength="100" name="wotemplateDescription" required="required" size="22" title="' .
	_('A description of the inventory wotemplate is required') .
	'" type="text" value="' . $_POST['wotemplateDescription'] .
	'" /></td></tr>';





if (!isset($Selectedwotemplate)) {
	$Selectedwotemplate='';
}
if (isset($Selectedwotemplate)) {
	//editing an existing stock wotemplate

	$sql = "SELECT wotemplatepropid,
					label,
					controltype,
					defaultvalue,
					numericvalue,
					
					minimumvalue,
					maximumvalue
			   FROM wotemplateproperties
			   WHERE wotemplateid='" . $Selectedwotemplate . "'
			   ORDER BY wotemplatepropid";

	$result = DB_query($sql, $db);

/*		echo '<br />Number of rows returned by the sql = ' . DB_num_rows($result) .
			'<br />The SQL was:<br />' . $sql;
*/
	echo '<br />
			<table class="selection">
				<tr>
					<th>' . _('Property Label') . '</th>
					<th>' . _('Control Type') . '</th>
					<th>' . _('Default Value') . '</th>
					<th>' . _('Numeric Value') . '</th>
					<th>' . _('Minimum Value') . '</th>
					<th>' . _('Maximum Value') . '</th>
					<th style = "position:fixed; left:-999px">' . _('Require in SO') . '</th>
				</tr>';
	$PropertyCounter =0;
	while ($myrow = DB_fetch_array($result)) {
		echo '<tr>
                <td><input type="hidden" name="PropID' . $PropertyCounter .'" value="' . $myrow['wotemplatepropid'] . '" />';
		echo '<input type="text" name="PropLabel' . $PropertyCounter . '" size="50" maxlength="100" value="' . $myrow['label'] . '" /></td>
				<td><select name="PropControlType' . $PropertyCounter . '">';
		if ($myrow['controltype']==0){
			echo '<option selected="selected" value="0">' . _('Text Box') . '</option>';
		} else {
			echo '<option value="0">' . _('Text Box') . '</option>';
		}
		if ($myrow['controltype']==1){
			echo '<option selected="selected" value="1">' . _('Select Box') . '</option>';
		} else {
			echo '<option value="1">' . _('Select Box') . '</option>';
		}
		if ($myrow['controltype']==2){
			echo '<option selected="selected" value="2">' . _('Check Box') . '</option>';
		} else {
			echo '<option value="2">' . _('Check Box') . '</option>';
		}
		if ($myrow['controltype']==3){
			echo '<option selected="selected" value="3">' . _('Date Box') . '</option>';
		} else {
			echo '<option value="3">' . _('Date Box') . '</option>';
		}
		echo '</select></td>
					<td><input type="text" name="PropDefault' . $PropertyCounter . '" value="' . $myrow['defaultvalue'] . '" /></td>';

		if ($myrow['numericvalue']==1){
			echo '<td><input type="checkbox" name="PropNumeric' . $PropertyCounter . '" checked="checked" /></td>';
		} else {
			echo '<td><input type="checkbox" name="PropNumeric' . $PropertyCounter . '" /></td>';
		}

		echo '<td><input type="text" name="PropMinimum' . $PropertyCounter . '" value="' . $myrow['minimumvalue'] . '" /></td>
				<td><input type="text" name="PropMaximum' . $PropertyCounter . '" value="' . $myrow['maximumvalue'] . '" /></td>';

		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DeleteProperty=' . $myrow['stkcatpropid'] .'&amp;Selectedwotemplate=' . $Selectedwotemplate . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this property? All properties of this type set up for stock items will also be deleted.') . '\');">' . _('Delete') . '</a></td>
			</tr>';

		$PropertyCounter++;
	} //end loop around defined properties for this wotemplate
	echo '<tr>
            <td><input type="hidden" name="PropID' . $PropertyCounter .'" value="NewProperty" />';
	echo '<input type="text" name="PropLabel' . $PropertyCounter . '" size="50" maxlength="100" /></td>
			<td><select name="PropControlType' . $PropertyCounter . '">
				<option selected="selected" value="0">' . _('Text Box') . '</option>
				<option value="1">' . _('Select Box') . '</option>
				<option value="2">' . _('Check Box') . '</option>
				<option value="3">' . _('Date Box') . '</option>
				</select></td>
			<td><input type="text" name="PropDefault' . $PropertyCounter . '" /></td>
			<td><input type="checkbox" name="PropNumeric' . $PropertyCounter . '" /></td>
			<td><input type="text" class="number" name="PropMinimum' . $PropertyCounter . '" /></td>
			<td><input type="text" class="number" name="PropMaximum' . $PropertyCounter . '" /></td>
			<td align="center"><input type="hidden" name="PropReqSO' . $PropertyCounter .'" /></td>
			</tr>';
	echo '</table>';
	echo '<input type="hidden" name="PropertyCounter" value="' . $PropertyCounter . '" />';

} /* end if there is a wotemplate selected */

echo '<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
    </div>
	</form>';

include('includes/footer.inc');
?>
