<?php

/* $Id: WWW_Users.php 6467 2013-12-02 03:19:37Z exsonqu $*/
include('includes/session.inc');

$Title = _('Change Password');

include('includes/header.inc');
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
if (mb_strstr($_POST['Password'],$_SESSION['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'),'error');
	} 
	}
	
	

		$UpdatePassword = '';
		if ($_POST['Password'] != ''){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "'";
		}
if ($_POST['Password'] == $_POST['ConfirmPassword'] and $_POST['ConfirmPassword']!=''){
$sql = "UPDATE www_users SET 
						" . $UpdatePassword . "
						
					WHERE userid = '". $_SESSION['UserID'] . "'";

		prnMsg( _('The selected user record has been updated'), 'success' );
	} 
	else
		prnMsg( _('Passwords should match'), 'error' );

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		unset($_POST['Password']);
	}


echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';



	echo '<table class="selection">';

if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
echo '<tr>
		<td>' . _('Enter New Password') . ':</td>
		<td><input type="password" pattern=".{5,}" name="Password" ' . (!isset($SelectedUser) ? 'required="required"' : '') . ' size="22" maxlength="20" value="' . $_POST['Password'] . '" placeholder="'._('At least 5 characters').'" title="'._('Passwords must be 5 characters or more and cannot same as the users id. A mix of upper and lower case and some non-alphanumeric characters are recommended.').'" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Confirm New Password') . ':</td>
		<td><input type="password" pattern=".{5,}" name="ConfirmPassword" ' . (!isset($SelectedUser) ? 'required="required"' : '') . ' size="22" maxlength="20" value="' . $_POST['Password'] . '" placeholder="'._('At least 5 characters').'" title="'._('Passwords must be 5 characters or more and cannot same as the users id. A mix of upper and lower case and some non-alphanumeric characters are recommended.').'" /></td>
	</tr>';

echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
    </div>
	</form>';

include('includes/footer.inc');
?>
