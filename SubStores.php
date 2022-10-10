<?php
/* $Id: SubStores.php 4567 2011-05-15 04:34:49Z daintree $*/

include('includes/session.inc');

$Title = _('SubStores');

include('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
		_('SubStores') . '" alt="" />' . ' ' . $Title . '</p>';

if ( isset($_GET['Selectedsubstoreid']) )
	$Selectedsubstoreid = $_GET['Selectedsubstoreid'];
elseif (isset($_POST['Selectedsubstoreid']))
	$Selectedsubstoreid = $_POST['Selectedsubstoreid'];

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (ContainsIllegalCharacters($_POST['SubStoreName'])) {
		$InputError = 1;
		prnMsg( _('The description of the SubStores must not contain the character') . " '&amp;' " . _('or the character') ." '",'error');
	}
	if (trim($_POST['SubStoreName']) == '') {
		$InputError = 1;
		prnMsg( _('The Name of the SubStores should not be empty'), 'error');
	}

	if (isset($_POST['Selectedsubstoreid'])
		AND $_POST['Selectedsubstoreid']!=''
		AND $InputError !=1) {


		/*Selectedsubstoreid could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM substores
				WHERE substoreid <> '" . $Selectedsubstoreid ."'
				AND description " . LIKE . " '" . $_POST['SubStoreName'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('This SubStores name already exists.'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very careful here

			$sql = "SELECT description
					FROM substores
					WHERE substoreid = '" . $Selectedsubstoreid . "'";
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_array($result);
				$OldSubStoreName = $myrow['description'];
				$sql = array();
				$sql[] = "UPDATE substores
							SET description='" . $_POST['SubStoreName'] . "',
								locid='" . $_POST['locid'] . "'
							WHERE description " . LIKE . " '" . $OldSubStoreName . "'";
			} else {
				$InputError = 1;
				prnMsg( _('The SubStores does not exist.'),'error');
			}
		}
		$msg = _('The SubStores has been modified');
	} elseif ($InputError !=1) {
		/*Selectedsubstoreid is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM substores
				WHERE description " . LIKE . " '" . $_POST['SubStoreName'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('There is already a SubStores with the specified name.'),'error');
		} else {
			$sql = "INSERT INTO SubStores (description,
											 locid )
					VALUES ('" . $_POST['SubStoreName'] . "',
							'" . $_POST['locid'] . "')";
		}
		$msg = _('The new SubStores has been created');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$ErrMsg = _('The SubStores could not be inserted');
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
	unset ($Selectedsubstoreid);
	unset ($_POST['Selectedsubstoreid']);
	unset ($_POST['SubStoreName']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button


	$sql = "SELECT description
			FROM substores
			WHERE substoreid = '" . $Selectedsubstoreid . "'";
	$result = DB_query($sql,$db);
/*
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('You cannot delete this SubStores'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldSubStoreName = $myrow[0];
		$sql= "SELECT COUNT(*)
				FROM stockrequest INNER JOIN substores
				ON stockrequest.ubStoreID=SubStores.substoreid
				WHERE description " . LIKE . " '" . $OldSubStoreName . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('You cannot delete this SubStores'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('There are items related to this SubStores');
		} else {*/
			$sql="DELETE FROM substores WHERE description " . LIKE . "'" . $OldSubStoreName . "'";
			$result = DB_query($sql,$db);
			prnMsg( $OldSubStoreName . ' ' . _('The SubStores has been removed') . '!','success');
		
	//end if account group used in GL accounts
	unset ($Selectedsubstoreid);
	unset ($_GET['Selectedsubstoreid']);
	unset($_GET['delete']);
	unset ($_POST['Selectedsubstoreid']);
	unset ($_POST['substoreid']);
	unset ($_POST['SubStoreName']);
}

 if (!isset($Selectedsubstoreid)) {

	$sql = "SELECT substoreid,
					description,
					locid
			FROM SubStores
			ORDER BY locid";

	$ErrMsg = _('There are no SubStores created');
	$result = DB_query($sql,$db,$ErrMsg);

	echo '<table class="selection">
			<tr>
				<th>' . _('SubStores Name') . '</th>
				<th>' . _('Main Store') . '</th>
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

		echo '<td>' . $myrow['description'] . '</td>
				<td>' . $myrow['locid'] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Selectedsubstoreid=' . $myrow['substoreid'] . '">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Selectedsubstoreid=' . $myrow['substoreid'] . '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this SubStores?') . '\');">'  . _('Delete')  . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!


if (isset($Selectedsubstoreid)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('View all SubStores') . '</a>
		</div>';
}

echo '<br />';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Selectedsubstoreid)) {
		//editing an existing section

		$sql = "SELECT substoreid,
						description,
						locid
				FROM substores
				WHERE substoreid='" . $Selectedsubstoreid . "'";

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('The selected substore could not be found.'),'warn');
			unset($Selectedsubstoreid);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['substoreid'] = $myrow['substoreid'];
			$_POST['SubStoreName']  = $myrow['description'];
			$Location			= $myrow['locid'];

			echo '<input type="hidden" name="Selectedsubstoreid" value="' . $_POST['substoreid'] . '" />';
			echo '<table class="selection">';
		}

	}  else {
		$_POST['SubStoreName']='';
		echo '<table class="selection">';
	}
	echo '<tr>
			<td>' . _('SubStores Name') . ':' . '</td>
			<td><input type="text" name="SubStoreName" size="50" required="required" title="' ._('The SubStores name is required') . '" maxlength="100" value="' . $_POST['SubStoreName'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Main Store') . '</td>
		
		<td><select name="locid">';
/*
	$usersql="SELECT userid FROM www_users";
	$userresult=DB_query($usersql,$db);
	while ($myrow=DB_fetch_array($userresult)) {
		if ($myrow['userid']==$AuthoriserID) {
			echo '<option selected="True" value="'.$myrow['userid'].'">' . $myrow['userid'] . '</option>';
		} else {
			echo '<option value="'.$myrow['userid'].'">' . $myrow['userid'] . '</option>';
		}
	}
	echo '</select></td>
*/	
	$SQL = "SELECT loccode,
					locationname
			FROM locations";

	$result = DB_query($SQL,$db);
	echo '<option value="">' . _('Not Yet Selected') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
	
			echo '<option value="';
		
		echo $myrow['loccode'] . '">' . ' - ' . $myrow['locationname'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';
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