<?php

/* $Id: PcClaimExpensesFromTab.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Claim Petty Cash Expenses From Tab');
/* webERP manual links before header.inc */
$ViewTopic= 'PettyCash';
$BookMark = 'ExpenseClaim';
include('includes/header.inc');
	$_SESSION['$ReceiptIDEdit'] ;
 $sql = "select max(counterindex) as i from pcashdetails";
 $result = DB_query($sql,$db);
 $row_result = DB_fetch_array($result);
 $ind = $row_result['i'] +1;
 if ( isset($_GET['SelectedIndex'])) {
		$sql = "SELECT *
				FROM pcashdetails
				WHERE counterindex='".$_GET['SelectedIndex']."'";

			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);

 $_SESSION['$ReceiptIDEdit'] = 'receipt_'.$_GET['SelectedIndex'];
 $_SESSION['clearpic'] = $_SESSION['part_pics_dir'] .'/'. 'receipt_'.$_GET['SelectedIndex'].'.pdf';

		}
		else
$ReceiptID = 'receipt_'.$ind;
 $_SESSION['clearpic'] = $_SESSION['part_pics_dir'] .'/'. $_SESSION['$ReceiptIDEdit'].'.pdf';
	
if (isset($_FILES['receipt']) AND $_FILES['receipt']['name'] !='') {

	$result	= $_FILES['receipt']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	if (isset ($_SESSION['$ReceiptIDEdit']))
 $filename = $_SESSION['part_pics_dir'] . '/' . $_SESSION['$ReceiptIDEdit'] . '.pdf';
else
	$filename = $_SESSION['part_pics_dir'] . '/' . $ReceiptID . '.pdf';

	 //But check for the worst
	if (mb_strtoupper(mb_substr(trim($_FILES['receipt']['name']),mb_strlen($_FILES['receipt']['name'])-3))!='PDF'){
		prnMsg(_('Only pdf files are supported - a file extension of .pdf is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['receipt']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['receipt']['type'] == 'text/plain' ) {  //File Type Check
		prnMsg( _('Only graphics files can be uploaded'),'warn');
		 	$UploadTheFile ='No';
    } elseif ( $_FILES['receipt']['error'] == 6 ) {  //upload temp directory check
		prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files. '),'warn');
		 	$UploadTheFile ='No';
	} elseif (file_exists($filename)){
		prnMsg(_('Attempting to overwrite an existing item image'),'warn');
		$result = unlink($filename);
		if (!$result){
			prnMsg(_('The existing image could not be removed'),'error');
			$UploadTheFile ='No';
		}
	}

	if ($UploadTheFile=='Yes'){
		$result  =  move_uploaded_file($_FILES['receipt']['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
	}
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;

if (isset($_POST['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}

if (isset($_POST['SelectedIndex'])){
	$SelectedIndex = $_POST['SelectedIndex'];
} elseif (isset($_GET['SelectedIndex'])){
	$SelectedIndex = $_GET['SelectedIndex'];
}

if (isset($_POST['Days'])){
	$Days = filter_number_format($_POST['Days']);
} elseif (isset($_GET['Days'])){
	$Days = filter_number_format($_GET['Days']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedTabs);
	unset($SelectedIndex);
	unset($Days);
	unset($_POST['Amount']);
	unset($_POST['Notes']);
	unset($_POST['meterreading']);
	unset($_POST['Receipt']);
}


if (isset($_POST['Process'])) {

	if ($_POST['SelectedTabs']=='') {
		echo prnMsg(_('You have not selected a tab to claim the expenses on'),'error');
		unset($SelectedTabs);
	}
}

if (isset($_POST['Go'])) {
	if ($Days<=0) {
		prnMsg(_('The number of days must be a positive number'),'error');
		$Days=30;
	}
}

if (isset($_POST['submit'])) {
//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if ($_POST['SelectedExpense']=='') {
		$InputError=1;
		prnMsg(_('You have not selected an expense to claim on this tab'),'error');
	} elseif ($_POST['Amount']==0) {
		$InputError = 1;
		prnMsg( _('The amount must be greater than 0'),'error');
	}

	if (isset($SelectedIndex) AND $InputError !=1)  {
		 $sql = "select lastreading from lastreading where expensecode like '".$_POST['SelectedExpense']."'";
		 $result = DB_query($sql,$db);
		$row_result = DB_fetch_array($result);
		 $average = $_POST['Amount']/($_POST['meterreading'] - $row_result['lastreading']);
		$sql = "UPDATE pcashdetails
			SET date = '".FormatDateForSQL($_POST['Date'])."',
			codeexpense = '" . $_POST['SelectedExpense'] . "',
			amount = '" .-filter_number_format($_POST['Amount']) . "',
			notes = '" . $_POST['Notes'] . "',
			meterreading = '" . $_POST['meterreading'] . "',
			
			receipt = '" . $_POST['Receipt'] . "'
			WHERE counterindex = '".$SelectedIndex."'";

		$msg = _('The Expense Claim on Tab') . ' ' . $SelectedTabs . ' ' .  _('has been updated');
if(isset($_POST['meterreading']))
	{	
echo	$sql2 = "update lastreading set lastreading =".$_POST['meterreading']." where expensecode like '".$_POST['SelectedExpense']."'";
		$result = DB_query($sql2,$db);
	}	
	} elseif ($InputError !=1 ) {

		// First check the type is not being duplicated
		// Add new record on submit
		 $sql = "select lastreading from lastreading where expensecode like '".$_POST['SelectedExpense']."'";
		 $result = DB_query($sql,$db);
		$row_result = DB_fetch_array($result);
		// $average = $_POST['Amount']/($_POST['meterreading'] - $row_result['lastreading']);
		$sql = "INSERT INTO pcashdetails (counterindex,
										tabcode,
										date,
										codeexpense,
										amount,
										authorized,
										posted,
										notes,
										receipt,
										lastreading,
										meterreading,
										
										receiptimage)
								VALUES ('" . $ind . "',
										
                                        '" . $_POST['SelectedTabs'] . "',
										'".FormatDateForSQL($_POST['Date'])."',
										'" . $_POST['SelectedExpense'] . "',
										'" . -filter_number_format($_POST['Amount']) . "',
										1,
										1,
										'" . $_POST['Notes'] . "',
										'" . $_POST['Receipt'] . "',
										'" . $row_result['lastreading'] . "',
										
										'" . $_POST['meterreading'] . "',
										
										
										'" . $ReceiptID . "'
										)";

		$msg = _('The Expense Claim on Tab') . ' ' . $_POST['SelectedTabs'] .  ' ' . _('has been created');
	if(isset($_POST['meterreading']))
	{
		$sql2 = "update lastreading set lastreading =".$_POST['meterreading']." where expensecode like '".$_POST['SelectedExpense']."'";
		$result = DB_query($sql2,$db);
	}
	

			$PeriodNo = GetPeriod(ConvertSQLDate(FormatDateForSQL($_POST['Date'])), $db);
$sql = "SELECT pcashdetails.counterindex,
				pcashdetails.tabcode,
				pcashdetails.date,
				pcashdetails.codeexpense,
				pcashdetails.amount,
				pcashdetails.authorized,
				pcashdetails.lastreading,
				pcashdetails.meterreading,
				
				pcashdetails.posted,
				pcashdetails.notes,
				pcashdetails.receipt,
				pcashdetails.receiptimage,
				pctabs.glaccountassignment,
				pctabs.glaccountpcash,
				pctabs.usercode,
				pctabs.currency,
				currencies.rate,
				pcexpenses.description,
				currencies.decimalplaces
			FROM ((pcashdetails inner join pctabs on pcashdetails.tabcode = pctabs.tabcode)
			inner join currencies on pctabs.currency = currencies.currabrev) left outer join 
			pcexpenses on pcashdetails.codeexpense = pcexpenses.codeexpense
			WHERE 
				 pcashdetails.counterindex = '" . $ind . "'";

	$result = DB_query($sql,$db);
	$myrow=DB_fetch_array($result)

			if ($myrow['rate'] == 1){ // functional currency
				$Amount = $myrow['amount'];
			}else{ // other currencies
				$Amount = $myrow['amount']/$myrow['rate'];
			}



//$Amount =-filter_number_format($_POST['Amount'])
			if ($myrow['codeexpense'] == 'ASSIGNCASH'){
				$type = 2;
				$AccountFrom = $myrow['glaccountassignment'];
				$AccountTo = $myrow['glaccountpcash'];
                $TagTo = 0;
			}else{
				$type = 1;
				$Amount = -$Amount;
				$AccountFrom = $myrow['glaccountpcash'];
				$SQLAccExp = "SELECT glaccount,
									tag
								FROM pcexpenses
								WHERE codeexpense = '".$myrow['codeexpense']."'";
				$ResultAccExp = DB_query($SQLAccExp,$db);
				$myrowAccExp = DB_fetch_array($ResultAccExp);
				$AccountTo = $myrowAccExp['glaccount'];
				$TagTo = $myrowAccExp['tag'];
			}

			//get typeno
			$typeno = GetNextTransNo($type,$db);

			//build narrative
			$Narrative= _('PettyCash') . ' - '. $myrow['tabcode'] . ' - ' . $myrow['codeexpense'] . ' - ' . DB_escape_string($myrow['notes']) . ' - ' . $myrow['receipt'];
			//insert to gltrans
			DB_Txn_Begin($db);

			$sqlFrom="INSERT INTO `gltrans` (`counterindex`,
											`type`,
											`typeno`,
											`chequeno`,
											`trandate`,
											`periodno`,
											`account`,
											`narrative`,
											`amount`,
											`posted`,
											`jobref`,
											`tag`)
									VALUES (NULL,
											'".$type."',
											'".$typeno."',
											0,
											'".$myrow['date']."',
											'".$PeriodNo."',
											'".$AccountFrom."',
											'". $Narrative ."',
											'".-$Amount."',
											0,
											'',
											'" . $TagTo ."')";

			if ($myrow['authorized'] == '0000-00-00')
			$ResultFrom = DB_Query($sqlFrom, $db, '', '', true);

			$sqlTo="INSERT INTO `gltrans` (`counterindex`,
										`type`,
										`typeno`,
										`chequeno`,
										`trandate`,
										`periodno`,
										`account`,
										`narrative`,
										`amount`,
										`posted`,
										`jobref`,
										`tag`)
								VALUES (NULL,
										'".$type."',
										'".$typeno."',
										0,
										'".$myrow['date']."',
										'".$PeriodNo."',
										'".$AccountTo."',
										'" . $Narrative . "',
										'".$Amount."',
										0,
										'',
										'" . $TagTo ."')";

				if ($myrow['authorized'] == '0000-00-00')
		
			$ResultTo = DB_Query($sqlTo, $db, '', '', true);

			if ($myrow['codeexpense'] == 'ASSIGNCASH'){
			// if it's a cash assignation we need to updated banktrans table as well.
				$ReceiptTransNo = GetNextTransNo( 2, $db);
				$SQLBank= "INSERT INTO banktrans (transno,
												type,
												bankact,
												ref,
												exrate,
												functionalexrate,
												transdate,
												banktranstype,
												amount,
												currcode)
										VALUES ('". $ReceiptTransNo . "',
											1,
											'" . $AccountFrom . "',
											'" . $Narrative . "',
											1,
											'" . $myrow['rate'] . "',
											'" . $myrow['date'] . "',
											'Cash',
											'" . -$myrow['amount'] . "',
											'" . $myrow['currency'] . "'
										)";
				$ErrMsg = _('Cannot insert a bank transaction because');
				$DbgMsg =  _('Cannot insert a bank transaction with the SQL');
					if ($myrow['authorized'] == '0000-00-00')
		
				$resultBank = DB_query($SQLBank,$db,$ErrMsg,$DbgMsg,true);

			}

			$sql = "UPDATE pcashdetails
					SET authorized = '".Date('Y-m-d')."',
					posted = 1
					WHERE counterindex = '".$myrow['counterindex']."'";
			$resultupdate = DB_query($sql,$db, '', '', true);
			DB_Txn_Commit($db);
					
	}

	if ( $InputError !=1) {
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');

		unset($_POST['SelectedExpense']);
		unset($_POST['Amount']);
		unset($_POST['Date']);
		unset($_POST['meterreading']);
		
		unset($_POST['Notes']);
		unset($_POST['Receipt']);
	}

} elseif ( isset($_GET['delete']) ) {


	$sql="DELETE FROM pcashdetails
			WHERE counterindex='".$SelectedIndex."'";
	$ErrMsg = _('Petty Cash Expense record could not be deleted because');
	$result = DB_query($sql,$db,$ErrMsg);
	prnMsg(_('Petty cash Expense record') .  ' ' . $SelectedTabs  . ' ' . _('has been deleted') ,'success');
	$sql="select * from pcashdetails order by meterreading desc";
			$result =  DB_query($sql,$db);
	
	error_reporting(E_ERROR | E_PARSE);
	$row_result = DB_fetch_array($result);
	
	if($row_result['meterreading']>0){
		$sql = "update lastreading set lastreading
	=".$row_result['meterreading']." where expensecode like '".$row_result['codeexpense']."'";
		$result =  DB_query($sql,$db);
	}
	
	
	$_SESSION['part_pics_dir'] .'/'. $_GET['SelectedIndex'].'.pdf';
	    @unlink($_SESSION['clearpic']);
			   
	unset($_GET['delete']);

}//end of get delete

if (!isset($SelectedTabs)){

	/* It could still be the first time the page has been run and a record has been selected for modification - SelectedTabs will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of sales types will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Payment Entry')	. '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form method="post" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br /><table class="selection">'; //Main table

	echo '<tr><td>' . _('Petty Cash Tabs for User ') . $_SESSION['UserID'] . ':</td>
				<td><select name="SelectedTabs">';

	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE usercode='" . $_SESSION['UserID'] . "'";

	$result = DB_query($SQL,$db);
	echo '<option value="">' . _('Not Yet Selected') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectTabs']) and $myrow['tabcode']==$_POST['SelectTabs']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['tabcode'] . '">' . $myrow['tabcode'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';
   	echo '</table>'; // close main table
    DB_free_result($result);

	echo '<br />
			<div class="centre">
				<input type="submit" name="Process" value="' . _('Accept') . '" />
				<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
			</div>
		</div>
	</form>';

} else { // isset($SelectedTabs)

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Petty Cash Claim Entry') . '" alt="" />
         ' . ' ' . $Title . '</p>';

	echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Select another tab') . '</a></div>';

	if (! isset($_GET['edit']) OR isset ($_POST['GO'])){
		echo '<form method="post" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
			<div>
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<br />
				<table class="selection">
				<tr>
					<th colspan="8"><h3>' . _('Petty Cash Tab') . ' ' .$SelectedTabs. '</h3></th>
				</tr>
				<tr>
					<th colspan="8">' . _('Detail Of Movements For Last ') .': ';


		if(!isset ($Days)){
			$Days=30;
		}

		/* Retrieve decimal places to display */
		$SqlDecimalPlaces="SELECT decimalplaces
					FROM currencies,pctabs
					WHERE currencies.currabrev = pctabs.currency
						AND tabcode='" . $SelectedTabs . "'";
		$result = DB_query($SqlDecimalPlaces,$db);
		$myrow=DB_fetch_array($result);
		$CurrDecimalPlaces = $myrow['decimalplaces'];

		echo '<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />
			<input type="text" class="integer" name="Days" value="' . $Days . '" maxlength="3" size="4" /> ' . _('Days');
		echo '<input type="submit" name="Go" value="' . _('Go') . '" />';
		echo '</th></tr>';

		if (isset($_POST['Cancel'])) {
			unset($_POST['SelectedExpense']);
			unset($_POST['Amount']);
			unset($_POST['Date']);
			unset($_POST['meterreading']);
			
			unset($_POST['Notes']);
			unset($_POST['Receipt']);
		}

		$sql = "SELECT * FROM pcashdetails
				WHERE tabcode='".$SelectedTabs."'
					AND date >=DATE_SUB(CURDATE(), INTERVAL ".$Days." DAY)
				ORDER BY date, counterindex ASC";

		$result = DB_query($sql,$db);

		echo '<tr>
				<th>' . _('Date Of Expense') . '</th>
				<th>' . _('Expense Description') . '</th>
				<th>' . _('Amount') . '</th>
				<th>' . _('Authorized') . '</th>
				<th>' . _('Last Reading') . '</th>
				
				<th>' . _('Meter Reading') . '</th>
				<th>' . _('Average') . '</th>
				
				<th>' . _('Notes') . '</th>
				<th>' . _('Receipt') . '</th>
				
			</tr>';

		$k=0; //row colour counter
$sum = 0;
$line = 0;
		while ($myrow = DB_fetch_row($result)) {
			if (($myrow['11']>0))
			{	
	$lastreading[] = $myrow['10'];
	 $meterreading = $myrow['11'];
	 $fuelpayment = $fuelpayment + $myrow[4];
	 "<br>";
		$sum = $sum + (-1*$myrow['4']/($myrow['11']-$myrow['10']));
					$line++;
			}
		if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			$sqldes="SELECT description
						FROM pcexpenses
						WHERE codeexpense='". $myrow['3'] . "'";

			$ResultDes = DB_query($sqldes,$db);
			$Description=DB_fetch_array($ResultDes);

			if (!isset($Description['0'])){
				$Description['0']='ASSIGNCASH';
			}
			if ($myrow['5']=='0000-00-00') {
				$AuthorisedDate=_('Unauthorised');
			} else {
				$AuthorisedDate=ConvertSQLDate($myrow['5']);
			}
			if (($myrow['5'] == '0000-00-00') and ($Description['0'] != 'ASSIGNCASH')){
				// only movements NOT authorized can be modified or deleted

			if (file_exists($_SESSION['part_pics_dir'] .'/receipt_'. $myrow['0'].'.pdf') )
			{
				if ($myrow['11']>0)
					{
			error_reporting(E_ERROR | E_PARSE);
	
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%sSelectedIndex=%s&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;edit=yes">' . _('Edit') . '</a></td>
					<td><a href="%sSelectedIndex=%s&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this code and the expenses it may have set up?') . '");\'>' . _('Delete') . '</a></td>
					</tr>',
					ConvertSQLDate($myrow['2']),
					$Description['0'],
					locale_number_format($myrow['4'],$CurrDecimalPlaces),
					$AuthorisedDate,
					$myrow['10'],
					$myrow['11'],
					-1*$myrow['4']/($myrow['11']-$myrow['10']),
					$myrow['7'],
					$myrow['0'],
					'' . _('') . '<a target = "_blank" href = "'.$_SESSION['part_pics_dir'] . '/receipt_' .$myrow['0'].'.pdf'.'">download</a>',
	
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['0'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['0']);
					}
				
			
			else
			
				{
				printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%sSelectedIndex=%s&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;edit=yes">' . _('Edit') . '</a></td>
					<td><a href="%sSelectedIndex=%s&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this code and the expenses it may have set up?') . '");\'>' . _('Delete') . '</a></td>
					</tr>',
					ConvertSQLDate($myrow['2']),
					$Description['0'],
					locale_number_format($myrow['4'],$CurrDecimalPlaces),
					$AuthorisedDate,
					'',
					'',
					'',
					$myrow['7'],
					$myrow['0'],
					'' . _('') . '<a target = "_blank" href = "'.$_SESSION['part_pics_dir'] . '/receipt_' .$myrow['0'].'.pdf'.'">download</a>',
	
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['0'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['0']);
			}
				}
				else
					{
					if ($myrow['11']>0)
					{
			error_reporting(E_ERROR | E_PARSE);
	
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td></td>
					<td><a href="%sSelectedIndex=%s&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;edit=yes">' . _('Edit') . '</a></td>
					<td><a href="%sSelectedIndex=%s&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this code and the expenses it may have set up?') . '");\'>' . _('Delete') . '</a></td>
					</tr>',
					ConvertSQLDate($myrow['2']),
					$Description['0'],
					locale_number_format($myrow['4'],$CurrDecimalPlaces),
					$AuthorisedDate,
					$myrow['10'],
					$myrow['11'],
					-1*$myrow['4']/($myrow['11']-$myrow['10']),
					$myrow['7'],
					$myrow['0'],
				
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['0'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['0']);
					}
				
			
			else
			
				{
				printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%sSelectedIndex=%s&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;edit=yes">' . _('Edit') . '</a></td>
					<td><a href="%sSelectedIndex=%s&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this code and the expenses it may have set up?') . '");\'>' . _('Delete') . '</a></td>
					</tr>',
					ConvertSQLDate($myrow['2']),
					$Description['0'],
					locale_number_format($myrow['4'],$CurrDecimalPlaces),
					$AuthorisedDate,
					'',
					'',
					'',
					$myrow['7'],
					$myrow['0'],
					
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['0'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['0']);
			}
				
				}
					
			}
			
				else
				{
					
			if (file_exists($_SESSION['part_pics_dir'] .'/receipt_'. $myrow['0'].'.pdf') )
			{
				if ($myrow['11']>0)
					{
			error_reporting(E_ERROR | E_PARSE);
	
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					ConvertSQLDate($myrow['2']),
					$Description['0'],
					locale_number_format($myrow['4'],$CurrDecimalPlaces),
					$AuthorisedDate,
					$myrow['10'],
					$myrow['11'],
					-1*$myrow['4']/($myrow['11']-$myrow['10']),
					$myrow['7'],
					$myrow['0'],
					'' . _('') . '<a target = "_blank" href = "'.$_SESSION['part_pics_dir'] . '/receipt_' .$myrow['0'].'.pdf'.'">download</a>');
					}
				
			
			else
			
				{
				printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					
					</tr>',
					ConvertSQLDate($myrow['2']),
					$Description['0'],
					locale_number_format($myrow['4'],$CurrDecimalPlaces),
					$AuthorisedDate,
					'',
					'',
					'',
					$myrow['7'],
					$myrow['0']);
			}
				}
				else
					{
					if ($myrow['11']>0)
					{
			error_reporting(E_ERROR | E_PARSE);
	
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td></td>
					</tr>',
					ConvertSQLDate($myrow['2']),
					$Description['0'],
					locale_number_format($myrow['4'],$CurrDecimalPlaces),
					$AuthorisedDate,
					$myrow['10'],
					$myrow['11'],
					-1*$myrow['4']/($myrow['11']-$myrow['10']),
					$myrow['7'],
					$myrow['0']);
					}
				
			
			else
			
				{
				printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					
					</tr>',
					ConvertSQLDate($myrow['2']),
					$Description['0'],
					locale_number_format($myrow['4'],$CurrDecimalPlaces),
					$AuthorisedDate,
					'',
					'',
					'',
					$myrow['7'],
					$myrow['0']);
			}
				
				}
			
				}
			
		}

		
		//END WHILE LIST LOOP

		$sqlAmount="SELECT sum(amount)
					FROM pcashdetails
					WHERE tabcode='".$SelectedTabs."'";

		$ResultAmount = DB_query($sqlAmount,$db);
		$Amount=DB_fetch_array($ResultAmount);

		if (!isset($Amount['0'])) {
			$Amount['0']=0;
		}
		if ($meterreading>0)
		{
		echo '<tr>
				<td colspan="2" style="text-align:right" >' . _('Current balance') . ':</td>
				<td class="number">' . locale_number_format($Amount['0'],$CurrDecimalPlaces) . '</td>
					<td colspan="2" style="text-align:right" >' . _('Average Milage') . ':</td>
				<td class="number">' . locale_number_format((-1*$fuelpayment/($meterreading - $lastreading[0])),$CurrDecimalPlaces) . '</td>
			
			</tr>
			<tr>
				
				</tr>';
		}
		else
		{
			echo '<tr>
				<td colspan="2" style="text-align:right" >' . _('Current balance') . ':</td>
				<td class="number">' . locale_number_format($Amount['0'],$CurrDecimalPlaces) . '</td>
			</tr>
			<tr>
				
			</tr>';
		
			
		}
		
			echo '</table>
			</div>
		</form>';
		}

	if (! isset($_GET['delete'])) {

		echo '<form name = "claimform" method="post" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
			<div>
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		if ( isset($_GET['edit'])) {
			$sql = "SELECT *
				FROM pcashdetails
				WHERE counterindex='".$SelectedIndex."'";

			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);

			$_POST['Date'] = ConvertSQLDate($myrow['date']);
			$_POST['SelectedExpense'] = $myrow['codeexpense'];
			$_POST['Amount']  =  -$myrow['amount'];
			$_POST['meterreading']  = $myrow['meterreading'];
			
			$_POST['Notes']  = $myrow['notes'];
			$_POST['Receipt']  = $myrow['receipt'];
			
			echo '<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />
				<input type="hidden" name="SelectedIndex" value="' . $SelectedIndex. '" />
				<input type="hidden" name="Days" value="' . $Days . '" />';

		}//end of Get Edit

		if (!isset($_POST['Date'])) {
			$_POST['Date']=Date($_SESSION['DefaultDateFormat']);
		}

        echo '<br /><table class="selection">'; //Main table
		echo '<tr>
				<td>' . _('Date Of Expense') . ':</td>
				<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="Date" size="10" required="required" autofocus="autofocus" maxlength="10" value="' . $_POST['Date']. '" /></td>
			</tr>
			<tr>
				<td>' . _('Code Of Expense') . ':</td>
				<td><select required="required" name="SelectedExpense" onchange="ReloadForm(claimform.UpdateForm)">';

		DB_free_result($result);

		$SQL = "SELECT pcexpenses.codeexpense,
					pcexpenses.description
			FROM pctabexpenses, pcexpenses, pctabs
			WHERE pctabexpenses.codeexpense = pcexpenses.codeexpense
				AND pctabexpenses.typetabcode = pctabs.typetabcode
				AND pctabs.tabcode = '".$SelectedTabs."'
			ORDER BY pcexpenses.codeexpense ASC";

		$result = DB_query($SQL,$db);
		echo '<option value="">' . _('Not Yet Selected') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_POST['SelectedExpense']) and $myrow['codeexpense']==$_POST['SelectedExpense']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $myrow['codeexpense'] . '">' . $myrow['codeexpense'] . ' - ' . $myrow['description'] . '</option>';

		} //end while loop

		echo '</select></td>
			</tr>';

		if (!isset($_POST['Amount'])) {
			$_POST['Amount']=0;
		}

		echo '<tr>
				<td>' . _('Amount') . ':</td>
				<td><input type="text" class="number" required="required" name="Amount" size="12" maxlength="11" value="' . $_POST['Amount'] . '" /></td>
			</tr>';
			if (isset($_POST['SelectedExpense']))
			{
			$sql = 'select description from pcexpenses where codeexpense = '.$_POST['SelectedExpense'];
			$result = DB_query($sql,$db);
			$row_result = DB_fetch_array($result);
			}
			if (strstr($row_result['description'],'Fuel') or strstr($row_result['description'],'Oil Change')
				
			or strstr($row_result['description'],'CNG'))
			{
				echo '<tr>
				<td>' . _('Meter Reading') . ':</td>
				<td><input type="text" class="number" required="required" name="meterreading" size="12" maxlength="11" value="' . $_POST['meterreading'] . '" /></td>
			</tr>';
				
			}
			if (!isset($_POST['meterreading'])) {
			$_POST['meterreading']='';
		}
		if (!isset($_POST['Notes'])) {
			$_POST['Notes']='';
		}

		echo '<tr>
				<td>' . _('Notes') . ':</td>
				<td><input type="text" name="Notes" size="50" maxlength="49" value="' . $_POST['Notes'] . '" /></td>
			</tr>';

		if (!isset($_POST['Receipt'])) {
			$_POST['Receipt']='';
		}

		//echo '<tr>
			//	<td>' . _('Receipt') . ':</td>
				//<td><input type="text" name="Receipt" size="50" maxlength="49" value="' . $_POST['Receipt'] . '" /></td>
			//</tr>';
		echo '<tr>
		<td>' .  _('Receipt Image (.pdf)') . ':</td>
		<td><input type="file" id="receipt" name="receipt" />';
		if (isset($_GET['SelectedIndex']))
		echo'<br /><input type="checkbox" name="ClearImage" id="ClearImage" value="1" > '._('Clear Image');
		echo '</td>';


		    if( isset($_SESSION['clearpic']) AND  !empty($_SESSION['clearpic']) AND file_exists($_SESSION['clearpic']) ) {
		$ReceiptImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/' . $myrow['receiptimage'] . '.pdf" height="100" width="100" />';
			
	if (isset($_POST['ClearImage']) ) {
			$_SESSION['clearpic'];
		    //workaround for many variations of permission issues that could cause unlink fail
		    @unlink($_SESSION['clearpic']);
			if(is_file($_SESSION['clearpic'])) {
                 prnMsg(_('You do not have access to delete this item image file.'),'error');
            } else {
    		    $ReceiptImgLink = _('No Image');
    		}
		}
	} else {
		$ReceiptImgLink = _('No Image');
	}


if ($ReceiptIDImgLink!=_('No Image')){
	
	}
echo '</tr>';
echo '</tr>';	
		echo	'</table>
			<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />
			<input type="hidden" name="Days" value="' .$Days. '" />
			<br />
			<div class="centre">
				<input type="submit" name="submit" value="' . _('Accept') . '" />
				<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
					<input type="submit" name="UpdateForm" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />

			</div>
			</div>
		</form>';

	} // end if user wish to delete

}
//unset ($_SESSION['ReceiptIDEdit']);
//unset ($_SESSION['clearpic']);
include('includes/footer.inc');
?>