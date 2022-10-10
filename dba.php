<?php
/* $Id: dba.php 4567 2011-05-15 04:34:49Z daintree $*/

include('includes/session.inc');

$Title = _('dba');

include('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
		_('dba') . '" alt="" />' . ' ' . $Title . '</p>';

if ( isset($_GET['Selecteddbaid']) )
	$Selecteddbaid = $_GET['Selecteddbaid'];
elseif (isset($_POST['Selecteddbaid']))
	$Selecteddbaid = $_POST['Selecteddbaid'];

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	/*if (ContainsIllegalCharacters($_POST['companyname'])) {
		$InputError = 1;
		prnMsg( _('The companyaddress of the dba must not contain the character') . " '&amp;' " . _('or the character') ." '",'error');
	}*/
	if (trim($_POST['companyname']) == '') {
		$InputError = 1;
		prnMsg( _('The Name of the dba should not be empty'), 'error');
	}

	if (isset($_POST['Selecteddbaid'])
		AND $_POST['Selecteddbaid']!=''
		AND $InputError !=1) {


		/*Selecteddbaid could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM dba
				WHERE dbaid <> '" . $Selecteddbaid ."'
				AND companyname " . LIKE . " '" . $_POST['companyname'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('This dba name already exists.'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very careful here

			$sql = "SELECT companyname,companyaddress
					FROM dba
					WHERE dbaid = '" . $Selecteddbaid . "'";
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_array($result);
				$Oldcompanyname = $myrow['companyname'];
				$sql = array();
				$sql[] = "UPDATE dba
							SET companyname='" . $_POST['companyname'] . "',
							companyaddress='" . $_POST['companyaddress'] . "'
							
							WHERE companyname " . LIKE . " '" . $Oldcompanyname . "'";
			} else {
				$InputError = 1;
				prnMsg( _('The dba does not exist.'),'error');
			}
		}
		$msg = _('The dba has been modified');
	} elseif ($InputError !=1) {
		/*Selecteddbaid is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM dba
				WHERE companyname " . LIKE . " '" . $_POST['companyname'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('There is already a dba with the specified name.'),'error');
		} else {
			$sql = "INSERT INTO dba (companyname,
											 companyaddress )
					VALUES ('" . $_POST['companyname'] . "',
							'" . $_POST['companyaddress'] . "')";
		}
		$msg = _('The new dba has been created');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$ErrMsg = _('The dba could not be inserted');
			$DbgMsg = _('The sql that failed was') . ':';
			foreach ($sql as $SQLStatement ) {
				$result = DB_query($SQLStatement,$db, $ErrMsg,$DbgMsg,true);
				if(!$result) {
					$InputError = 1;
					break;
				}
			}
			if ($InputError!=1){
				$result = DB_Txn_Commit($db);
			} else {
				$result = DB_Txn_Rollback($db);
			}
		} else {
			$result = DB_query($sql,$db);
		}
		prnMsg($msg,'success');
        echo '<br />';
	}
	unset ($Selecteddbaid);
	unset ($_POST['Selecteddbaid']);
	unset ($_POST['companyname']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button


	$sql = "SELECT companyname,companyaddress
			FROM dba
			WHERE dbaid = '" . $Selecteddbaid . "'";
	$result = DB_query($sql,$db);

	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('You cannot delete this dba'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$Oldcompanyname = $myrow[0];
			$sql="DELETE FROM dba WHERE companyname " . LIKE . "'" . $Oldcompanyname . "'";
			$result = DB_query($sql,$db);
			prnMsg( $Oldcompanyname . ' ' . _('The dba has been removed') . '!','success');
	}
	//end if account group used in GL accounts
	unset ($Selecteddbaid);
	unset ($_GET['Selecteddbaid']);
	unset($_GET['delete']);
	unset ($_POST['Selecteddbaid']);
	unset ($_POST['dbaid']);
	unset ($_POST['companyname']);
}

 if (!isset($Selecteddbaid)) {

	$sql = "SELECT dbaid,
					companyname,
					companyaddress
			FROM dba
			ORDER BY companyname";

	$ErrMsg = _('There are no dba created');
	$result = DB_query($sql,$db,$ErrMsg);

	echo '<table class="selection">
			<tr>
				<th>' . _('Company Name') . '</th>
				<th>' . _('Company Address') . '</th>
			</tr>';

	$k=0; //row colour counter
	while ($myrow = DB_fetch_array($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		echo '<td>' . $myrow['companyname'] . '</td>
				<td>' . $myrow['companyaddress'] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Selecteddbaid=' . $myrow['dbaid'] . '">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Selecteddbaid=' . $myrow['dbaid'] . '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this dba?') . '\');">'  . _('Delete')  . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!


if (isset($Selecteddbaid)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('View all dba') . '</a>
		</div>';
}

echo '<br />';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Selecteddbaid)) {
		//editing an existing section

		$sql = "SELECT dbaid,
						companyname,
						companyaddress
				FROM dba
				WHERE dbaid='" . $Selecteddbaid . "'";

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('The selected dba could not be found.'),'warn');
			unset($Selecteddbaid);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['dbaid'] = $myrow['dbaid'];
			$_POST['companyname']  = $myrow['companyname'];
			$_POST['companyaddress']		= $myrow['companyaddress'];

			echo '<input type="hidden" name="Selecteddbaid" value="' . $_POST['dbaid'] . '" />';
			echo '<table class="selection">';
		}

	}  else {
		$_POST['companyname']='';
		echo '<table class="selection">';
	}
	echo '<tr>
			<td>' . _('Company Name') . ':' . '</td>
			<td><input type="text" name="companyname" size="100" required="required" title="' ._('The dba name is required') . '" maxlength="100" value="' . $_POST['companyname'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Company Address') . ':' . '</td>
			<td><input type="text" name="companyaddress" size="100" required="required" title="' ._('The dba name is required') . '" maxlength="200" value="' . $_POST['companyaddress'] . '" /></td>
		</tr>
	';
echo '
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>