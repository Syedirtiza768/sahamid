<?php

/* $Id: WWW_Users.php 6467 2013-12-02 03:19:37Z exsonqu $*/
include('includes/DefineSalesCaseClass.php');
include('includes/session.inc');

if (isset($_GET['salescaseref'])) {
	$Title = _('Modify Sales Case') . ' ' . $_GET['salescaseref'];
} else {
	$Title = _('Sales Case Entry');
}

if (isset($_GET['CustomerID'])) {
	$_POST['SelectedCustomer']=$_GET['CustomerID'];
	
	$_SESSION['SelectedCustomer']=$_POST['SelectedCustomer'];
}


foreach ($_POST as $FormVariableName=>$FormVariableValue) {
	if (mb_substr($FormVariableName, 0, 6)=='Submit') {
		$Index = mb_substr($FormVariableName, 6);
		$_POST['SelectedCustomer']=$_POST['SelectedCustomer'.$Index];
		$_POST['SelectedBranch']=$_POST['SelectedBranch'.$Index];
		$_SESSION['SelectedBranch']=$_POST['SelectedBranch'];
		
	$_SESSION['SelectedCustomer']=$_POST['SelectedCustomer'];
	}
}
/*if (!isset($_SESSION['salescaseref']))
{
$_SESSION['salescaseref'] = $_POST['SelectedBranch'].date(':m-d-y:his');
}
else 
{
	$_SESSION['salescaseref'] = $_GET['salescaseref'];
} */
if (isset($_GET['salescaseref']))
{
	$salescaseref = $_GET['salescaseref'];

}
else 
{
	$salescaseref = $_POST['SelectedBranch'].date('-Y-m-d--his');

} 

$ViewTopic= 'Sales Case';
$BookMark = 'CreateSalesCase';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['SubmitComment']))
{
	$sql = "INSERT INTO salescasecomments (salescaseref,comment,username)
	
	VALUES('".$salescaseref."','".$_POST['comment']."','".$_SESSION['UsersRealName']."')";
		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the incoming stock cannot be added because');
				$DbgMsg =  _('The following SQL to insert the stock movement record was used');
				$ResultX = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
	





}
		for ($i=0; $i<=$_SESSION['maxdispatch']; $i++)

{
	
	if (isset ($_POST['sendinvoice'.$i]))
	{
		$sql = 'update dc set dcstatus = "'.$_POST['sendinvoice'.$i].'" where dispatchid = '.$i.'';
		DB_query($sql, $db);
	}
}

//print_r($_SESSION['SalesCase'.$_POST['salescaseref']]);
/*If the page is called is called without an salescaseref being set then
 * it must be either a new SalesCase, or the start of a modification of an
 * existing SalesCase, and so we must create a new salescaseref.
 *
 * The salescaseref only needs to be unique for this php session, so a
 * unix timestamp will be sufficient.
 */



if (isset($_GET['NewSalesCase']) AND isset($_SESSION['SalesCase'.$_POST['salescaseref']])){
	unset($_SESSION['SalesCase'.$_POST['salescaseref']]);
//	$_SESSION['salescaseref'] =$_POST['SelectedBranch'].date(':m-d-y:his');
;
	$_SESSION['ExistingSalesCase'] = 0;
	//unset($_SESSION['salescaseref']);
}
//print_r($_FILES);

if (isset($_GET['NewSalesCase']) AND isset($_GET['SelectedCustomer']) ) {
	/*
	* initialize a new SalesCase
	*/
	$_SESSION['ExistingSalesCase']=0;
	unset($_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseBOM);
	unset($_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseReqts);
	unset($_SESSION['SalesCase'.$_POST['salescaseref']]);
	/* initialize new class object */
	$_SESSION['SalesCase'.$_POST['salescaseref']] = new SalesCase;

	$_POST['SelectedCustomer'] = $_GET['SelectedCustomer'];

	/*The customer is checked for credit and the SalesCase Object populated
	 * using the usual logic of when a customer is selected
	 * */
}

if (!isset($_SESSION['SalesCase'.$_POST['salescaseref']])){
	/* It must be a new SalesCase being created
	 * $_SESSION['SalesCase'.$_POST['salescaseref']] would be set up from the order modification
	 * code above if a modification to an existing SalesCase. Also
	 * $ExistingSalesCase would be set to the SalesCaseRef
	 * */
		$_SESSION['ExistingSalesCase']= 0;
		$_SESSION['SalesCase'.$_POST['salescaseref']] = new SalesCase;
		$_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef = $salescaseref;
		if ($_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo==''
				OR !isset($_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo)){

/* a session variable will have to maintain if a supplier
 * has been selected for the order or not the session
 * variable CustomerID holds the supplier code already
 * as determined from user id /password entry  */
			$_SESSION['RequireCustomerSelection'] = 1;
		} else {
			$_SESSION['RequireCustomerSelection'] = 0;
		}
}
$sql = 'SELECT * from salescase where salescaseref = "'.$salescaseref.'"';
$result = DB_query($sql,$db);
if(DB_num_rows($result)==1 )
{
	$row_result = DB_fetch_array($result);
		$_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef = $row_result['salescaseref'];
		$_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseDescription = $row_result['salescasedescription'];
		$_SESSION['SalesCase'.$_POST['salescaseref']]->commencementdate = $row_result['commencementdate'];
		$_SESSION['SalesCase'.$_POST['salescaseref']]->salesman = $row_result['salesman'];
		$_SESSION['SalesCase'.$_POST['salescaseref']]->salescasevalue = $row_result['value'];
		$_SESSION['SalesCase'.$_POST['salescaseref']]->salescaseprospects = $row_result['prospect'];
		$_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo = $row_result['debtorno'];
		$_SESSION['SalesCase'.$_POST['salescaseref']]->BranchCode = $row_result['branchcode'];
		$sql = "SELECT debtorsmaster.name,
					custbranch.brname,
					debtorsmaster.currcode,
					debtorsmaster.holdreason,
					holdreasons.dissallowinvoices,
					currencies.rate
			FROM debtorsmaster INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason=holdreasons.reasoncode
			WHERE debtorsmaster.debtorno='" . $_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo  . "'
			AND custbranch.branchcode='" . $_SESSION['SalesCase'.$_POST['salescaseref']]->BranchCode . "'" ;

	$ErrMsg = _('The customer record selected') . ': ' . $_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_array($result);
	if (DB_num_rows($result)==0){
		prnMsg(_('The customer details were unable to be retrieved'),'error');
		if ($debug==1){
			prnMsg(_('The SQL used that failed to get the customer details was:') . '<br />' . $sql,'error');
		}
	} else {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->BranchName = $myrow['brname'];
		$_SESSION['RequireCustomerSelection'] = 0;
		$_SESSION['SalesCase'.$_POST['salescaseref']]->CustomerName = $myrow['name'];
		

		if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn and 2 for prohibit SalesCase */
			$CreditAvailable = GetCreditAvailable($_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo,$db);
			if ($_SESSION['CheckCreditLimits']==1 AND $CreditAvailable <=0){
				prnMsg(_('The') . ' ' . $_SESSION['SalesCase'.$_POST['salescaseref']]->CustomerName . ' ' . _('account is currently at or over their credit limit'),'warn');
			} elseif ($_SESSION['CheckCreditLimits']==2 AND $CreditAvailable <=0){
				prnMsg(_('No more orders can be placed by') . ' ' . $myrow[0] . ' ' . _(' their account is currently at or over their credit limit'),'warn');
				include('includes/footer.inc');
				exit;
			}
		}
	} //a customer was retrieved ok
} //end if a customer 



else
{
if (isset($_POST['salescaseref'])) {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef = $_POST['salescaseref'];
	
}
if (isset($_POST['SalesCaseDescription'])) {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseDescription = $_POST['SalesCaseDescription'];
	
}
if (isset($_POST['commencementdate'])) {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->commencementdate = $_POST['commencementdate'];
	
}
if (isset($_POST['salesman'])) {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->salesman = $_POST['salesman'];
	
}
if (isset($_POST['salescasevalue'])) {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->salescasevalue = $_POST['salescasevalue'];
	
}
if (isset($_POST['salescaseprospect'])) {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->salescaseprospects = $_POST['salescaseprospect'];
	
}
if (isset($_SESSION['CustomerID'])) {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo = $_SESSION['CustomerID'];
	
}
if (isset($_SESSION['BranchCode'])) {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->BranchCode = $_SESSION['BranchCode'];
	
}
$_SESSION['SalesCase'.$_POST['salescaseref']]->commencementdate = date('Y-m-d h:i:s');

}

//print_r($_SESSION['SalesCase'.$_POST['salescaseref']]);
//customer search
if (isset($_POST['SearchCustomers'])){

	if (($_POST['CustKeywords']!='') AND (($_POST['CustCode']!='') OR ($_POST['CustPhone']!=''))) {
		prnMsg( _('Customer Branch Name keywords have been used in preference to the Customer Branch Code or Branch Phone Number entered'), 'warn');
	}
	if (($_POST['CustCode']!='') AND ($_POST['CustPhone']!='')) {
		prnMsg(_('Customer Branch Code has been used in preference to the Customer Branch Phone Number entered'), 'warn');
	}
	if (mb_strlen($_POST['CustKeywords'])>0) {
	//insert wildcard characters in spaces
		$_POST['CustKeywords'] = mb_strtoupper(trim($_POST['CustKeywords']));
		$SearchString = '%' . str_replace(' ', '%', $_POST['CustKeywords']) . '%';
if ($_SESSION['AccessLevel'] == 14)
{
		$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
					INNER JOIN salesman
					    ON custbranch.salesman=salesman.salesmancode
					WHERE custbranch.brname " . LIKE . " '$SearchString'
						AND custbranch.disabletrans=0
						AND salesman.salesmanname='".$_SESSION['UsersRealName']."'
					ORDER BY custbranch.debtorno, custbranch.branchcode";
}
else
	
	{
			if ($_SESSION['AccessLevel'] == 14)
{
			$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
						INNER JOIN salesman
					    ON custbranch.salesman=salesman.salesmancode
					
					WHERE custbranch.brname " . LIKE . " '$SearchString'
						AND custbranch.disabletrans=0
							AND salesman.salesmanname='".$_SESSION['UsersRealName']."'
					
					ORDER BY custbranch.debtorno, custbranch.branchcode";
}
else
{
	$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
					WHERE custbranch.brname " . LIKE . " '$SearchString'
						AND custbranch.disabletrans=0
					ORDER BY custbranch.debtorno, custbranch.branchcode";
	
	
}

	}
	} elseif (mb_strlen($_POST['CustCode'])>0){

		$_POST['CustCode'] = mb_strtoupper(trim($_POST['CustCode']));
if ($_SESSION['AccessLevel'] == 14)
{
		$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
							INNER JOIN salesman
					    ON custbranch.salesman=salesman.salesmancode
					
					WHERE custbranch.branchcode " . LIKE . " '%" . $_POST['CustCode'] . "%'
						AND custbranch.disabletrans=0
								AND salesman.salesmanname='".$_SESSION['UsersRealName']."'
					
					ORDER BY custbranch.debtorno";
}
else
	
	{
		
		$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
					WHERE custbranch.branchcode " . LIKE . " '%" . $_POST['CustCode'] . "%'
						AND custbranch.disabletrans=0
					ORDER BY custbranch.debtorno";
		
		
		
	}


	} elseif (mb_strlen($_POST['CustPhone'])>0){
		
		
	if ($_SESSION['AccessLevel'] == 14)
{	
		$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
						INNER JOIN salesman
					    ON custbranch.salesman=salesman.salesmancode
						
					WHERE custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
						AND custbranch.disabletrans=0
								AND salesman.salesmanname='".$_SESSION['UsersRealName']."'
					
					ORDER BY custbranch.debtorno";
					
}
else
{
	$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
						
					WHERE custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
						AND custbranch.disabletrans=0
					ORDER BY custbranch.debtorno";
	
	
}


	} else {
		
		
		if ($_SESSION['AccessLevel'] == 14)
{	
		$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba
					FROM custbranch
					LEFT JOIN debtorsmaster
						
						ON custbranch.debtorno=debtorsmaster.debtorno
						INNER JOIN salesman
					    ON custbranch.salesman=salesman.salesmancode
						
					WHERE custbranch.disabletrans=0
					AND salesman.salesmanname='".$_SESSION['UsersRealName']."'
					
					ORDER BY custbranch.debtorno";
}
else
{
	$SQL = "SELECT custbranch.brname,
						custbranch.contactname,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.branchcode,
						custbranch.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba
					FROM custbranch
					LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
					WHERE custbranch.disabletrans=0
						
					ORDER BY custbranch.debtorno";
	
	
	
}


	}

	$ErrMsg = _('The searched customer records requested cannot be retrieved because');
	$result_CustSelect = DB_query($SQL,$db,$ErrMsg);

	if (DB_num_rows($result_CustSelect)==0){
		prnMsg(_('No Customer Branch records contain the search criteria') . ' - ' . _('please try again') . ' - ' . _('Note a Customer Branch Name may be different to the Customer Name'),'info');
	}
} /*one of keywords or custcode was more than a zero length string */

if (isset($_POST['SelectedCustomer'])) {

/* will only be true if page called from customer selection form
 * or set because only one customer record returned from a search
 * so parse the $Select string into debtorno and branch code */


	$_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo  = $_POST['SelectedCustomer'];
	$_SESSION['SalesCase'.$_POST['salescaseref']]->BranchCode = $_POST['SelectedBranch'];

	$sql = "SELECT debtorsmaster.name,
					custbranch.brname,
					debtorsmaster.currcode,
					debtorsmaster.holdreason,
					holdreasons.dissallowinvoices,
					currencies.rate
			FROM debtorsmaster INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason=holdreasons.reasoncode
			WHERE debtorsmaster.debtorno='" . $_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo  . "'
			AND custbranch.branchcode='" . $_SESSION['SalesCase'.$_POST['salescaseref']]->BranchCode . "'" ;

	$ErrMsg = _('The customer record selected') . ': ' . $_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_array($result);
	if (DB_num_rows($result)==0){
		prnMsg(_('The customer details were unable to be retrieved'),'error');
		if ($debug==1){
			prnMsg(_('The SQL used that failed to get the customer details was:') . '<br />' . $sql,'error');
		}
	} else {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->BranchName = $myrow['brname'];
		$_SESSION['RequireCustomerSelection'] = 0;
		$_SESSION['SalesCase'.$_POST['salescaseref']]->CustomerName = $myrow['name'];
		

		if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn and 2 for prohibit SalesCase */
			$CreditAvailable = GetCreditAvailable($_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo,$db);
			if ($_SESSION['CheckCreditLimits']==1 AND $CreditAvailable <=0){
				prnMsg(_('The') . ' ' . $_SESSION['SalesCase'.$_POST['salescaseref']]->CustomerName . ' ' . _('account is currently at or over their credit limit'),'warn');
			} elseif ($_SESSION['CheckCreditLimits']==2 AND $CreditAvailable <=0){
				prnMsg(_('No more orders can be placed by') . ' ' . $myrow[0] . ' ' . _(' their account is currently at or over their credit limit'),'warn');
				include('includes/footer.inc');
				exit;
			}
		}
	} //a customer was retrieved ok
} //end if a customer has just been selected
 $sql = 'SELECT * from salescase where salescaseref = "'.$salescaseref.'"';
 DB_num_rows($result);
$result = DB_query($sql,$db);

$count=0;
if((DB_num_rows($result)<1) AND isset($_POST['submitsalescase']))
{
	
	//`salescaseref`, `salescasedescription`, `debtorno`, `branchcode`, `loccode`,
//	`date`, `enquiryfile`, `enquirydate`, `pofile`, `podate`, `ocdocumentfile`, `ocdocumentdate` commencementdate
$sqldelcase = "DELETE FROM salescase where salescaseref = '" . $_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef . "'";
DB_query($sqldelcase,$db);

 $sqlsalescase="INSERT INTO salescase (salescaseref,
										salescasedescription,
										salesman,
											debtorno,
											branchcode,
											commencementdate,
											
											value,
											prospect
											)
										VALUES(
											'" . $_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef . "',
											'" . $_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseDescription. "',
											'" . $_SESSION['SalesCase'.$_POST['salescaseref']]->salesman = $_POST['salesman']
											. "',
											
											'" . $_SESSION['SelectedCustomer']. "',
											'" . $_SESSION['SelectedBranch']. "',
											
											
												'" . date('Y-m-d h:i:s')  . "',
											
											'" . $_SESSION['SalesCase'.$_POST['salescaseref']]->salescasevalue. "',
											
											
											'" . $_SESSION['SalesCase'.$_POST['salescaseref']]->salescaseprospects . "')";
	
	 
	DB_query($sqlsalescase,$db);
}
else
{			
				//`salescaseref`, `salescasedescription`, `debtorno`, `branchcode`, `loccode`,
//	`date`, `enquiryfile`, `enquirydate`, `pofile`, `podate`, `ocdocumentfile`, `ocdocumentdate` commencementdate

	$sql = "UPDATE salescase
						SET 
						
							salescasedescription='" . $_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseDescription . "'
							
					WHERE salescaseref='".$_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef."'";
	
	
	DB_query($sql,$db);
	
	
	
	
}

// enquiry file upload implementation begin
$fname = 'enquiryfile';
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

	$result	= $_FILES['enquiryfile']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.$salescaseref.urlencode($_SESSION[UsersRealName]).'.pdf';
	
	 //But check for the worst
	if (mb_strtoupper(mb_substr(trim($_FILES[$fname]['name']),mb_strlen($_FILES[$fname]['name'])-3))!='PDF'){
		prnMsg(_('Only pdf files are supported - a file extension of .pdf is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES[$fname]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES[$fname]['error'] == 6 ) {  //upload temp directory check
		prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files. '),'warn');
		 	$UploadTheFile ='No';
	} elseif (file_exists($filename)){
		prnMsg(_('Attempting to overwrite an existing file'),'warn');
		$result = unlink($filename);
		if (!$result){
			prnMsg(_('The existing file could not be removed'),'error');
			$UploadTheFile ='No';
		}
	}

	if ($UploadTheFile=='Yes'){
	if (	$result  =  move_uploaded_file($_FILES[$fname]['tmp_name'], $filename))
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
		$enquirydate = date('Y-m-d h:i:s');
	
								
	
	$sqlA = "UPDATE salescase
						SET 
						
							enquirydate='" .$enquirydate. "'
							
					WHERE salescaseref='".$salescaseref."'";
	
	DB_query($sqlA,$db);
	}
}



if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;
//enquiry file upload implementation end
// Quotation files upload implementation begin
$fname = 'quotationfile';
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

	$result	= $_FILES['quotationfile']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . 'quotationfile_'.$salescaseref.date('+his') .urlencode($_SESSION[UsersRealName]).'.pdf';
	
	
	
	 //But check for the worst
	if (mb_strtoupper(mb_substr(trim($_FILES[$fname]['name']),mb_strlen($_FILES[$fname]['name'])-3))!='PDF'){
		prnMsg(_('Only pdf files are supported - a file extension of .pdf is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES[$fname]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES[$fname]['error'] == 6 ) {  //upload temp directory check
		prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files. '),'warn');
		 	$UploadTheFile ='No';
	} elseif (file_exists($filename)){
		prnMsg(_('Attempting to overwrite an existing file'),'warn');
		$result = unlink($filename);
		if (!$result){
			prnMsg(_('The existing file could not be removed'),'error');
			$UploadTheFile ='No';
		$quotationdate = date('Y-m-d');
	
		}
	}

	if ($UploadTheFile=='Yes'){
		$result  =  move_uploaded_file($_FILES[$fname]['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
	$quotationdate = date('Y-m-d h:i:s');
		$sql = 'INSERT INTO salescasequotations(
													`salescaseref`
													, `quotationdate`
												
		
												)	
												
												VALUES(
														'.$salescaseref.',
														'.$quotationdate.')';
												
												
	
	$sqlB = "UPDATE salescase
						SET 
						
							lastquotationdate='" .date('Y-m-d h:i:s'). "'
							
					WHERE salescaseref='".$salescaseref."'";
	
	
	DB_query($sqlB,$db) or die(mysql_error());;
	
													
													
										
	
	
	}
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;
//Quotation files upload implementation end
// Purchase order file upload implementation begin
$fname = 'pofile';
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

	$result	= $_FILES['pofile']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . 'pofile_'.$salescaseref.urlencode($_SESSION[UsersRealName]).'.pdf';
	
	 //But check for the worst
	if (mb_strtoupper(mb_substr(trim($_FILES[$fname]['name']),mb_strlen($_FILES[$fname]['name'])-3))!='PDF'){
		prnMsg(_('Only pdf files are supported - a file extension of .pdf is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES[$fname]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES[$fname]['error'] == 6 ) {  //upload temp directory check
		prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files. '),'warn');
		 	$UploadTheFile ='No';
	} elseif (file_exists($filename)){
		prnMsg(_('Attempting to overwrite an existing file'),'warn');
		$result = unlink($filename);
		if (!$result){
			prnMsg(_('The existing file could not be removed'),'error');
			$UploadTheFile ='No';
		}
	}

	if ($UploadTheFile=='Yes'){
		$result  =  move_uploaded_file($_FILES[$fname]['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
		$podate = date('Y-m-d h:i:s');
		
	$sqlC = "UPDATE salescase
						SET 
						
							podate='" .$podate. "'
							
					WHERE salescaseref='".$salescaseref."'";
	
	
	DB_query($sqlC,$db);
	
	}
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;
//Purchase order file upload implementation end
// Order confirmation file upload implementation begin
$fname = 'ocfile';
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

	$result	= $_FILES['ocfile']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . 'ocfile_'.$salescaseref.urlencode($_SESSION[UsersRealName]).'.pdf';
	
	 //But check for the worst
	if (mb_strtoupper(mb_substr(trim($_FILES[$fname]['name']),mb_strlen($_FILES[$fname]['name'])-3))!='PDF'){
		prnMsg(_('Only pdf files are supported - a file extension of .pdf is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES[$fname]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES[$fname]['error'] == 6 ) {  //upload temp directory check
		prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files. '),'warn');
		 	$UploadTheFile ='No';
	} elseif (file_exists($filename)){
		prnMsg(_('Attempting to overwrite an existing file'),'warn');
		$result = unlink($filename);
		if (!$result){
			prnMsg(_('The existing file could not be removed'),'error');
			$UploadTheFile ='No';
		}
	}

	if ($UploadTheFile=='Yes'){
		$result  =  move_uploaded_file($_FILES[$fname]['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
	$ocdate = date('Y-m-d h:i:s');
	$sqlD = "UPDATE salescase
						SET 
						
							ocdocumentdate='" .$ocdate. "'
							
					WHERE salescaseref='".$salescaseref."'";
	
	
	DB_query($sqlD,$db);
	}
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;
//Order Confirmation file upload implementation end
// RPP files upload implementation begin
$fname = 'rppfile';
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

	$result	= $_FILES['rppfile']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . 'rppfile_'.$salescaseref.date('+his') .urlencode($_SESSION[UsersRealName]).'.pdf';

	 //But check for the worst
	if (mb_strtoupper(mb_substr(trim($_FILES[$fname]['name']),mb_strlen($_FILES[$fname]['name'])-3))!='PDF'){
		prnMsg(_('Only pdf files are supported - a file extension of .pdf is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES[$fname]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES[$fname]['error'] == 6 ) {  //upload temp directory check
		prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files. '),'warn');
		 	$UploadTheFile ='No';
	} elseif (file_exists($filename)){
		prnMsg(_('Attempting to overwrite an existing file'),'warn');
		$result = unlink($filename);
		if (!$result){
			prnMsg(_('The existing file could not be removed'),'error');
			$UploadTheFile ='No';
		}
	}

	if ($UploadTheFile=='Yes'){
		$result  =  move_uploaded_file($_FILES[$fname]['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
		$rppdate = date('Y-m-d');
	
	}
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;
//RPP files upload implementation end




if ((!isset($_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo)
		OR $_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo=='' ) AND !isset($_POST['submitsalescase'])
		 AND !isset($_GET['salescaseref'])
	){

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/SalesCase.png" title="' . _('SalesCase') . '" alt="" />' . ' ' . _('SalesCase: Select Customer') . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .'" name="CustomerSelection" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table cellpadding="3" class="selection">
			<tr>
			<td style="font-weight:bold; font-size:18px;"><h5>' . _('Part of the Customer Branch Name') . ':</h5></td>
			<td style="font-weight:bold; font-size:18px;"><input tabindex="1" type="text" name="CustKeywords" autofocus="autofocus" size="20" maxlength="25" /></td>
			<td style="font-weight:bold; font-size:18px;"><h2><b>' . _('OR') . '</b></h2></td>
			<td style="font-weight:bold; font-size:18px;"><h5>' .  _('Part of the Customer Branch Code'). ':</h5></td>
			<td style="font-weight:bold; font-size:18px;"><input tabindex="2" type="text" name="CustCode" data-type="no-illegal-chars" title="' . _('Enter an extract of the customer code to search for. Customer codes can only contain alpha-numeric characters, underscore or hyphens') . '" size="15" maxlength="18" /></td>
			<td style="font-weight:bold; font-size:18px;"><h2><b>' . _('OR') . '</b></h2></td>
			<td style="font-weight:bold; font-size:18px;"><h5>' . _('Part of the Branch Phone Number') . ':</h5></td>
			<td style="font-weight:bold; font-size:18px;"><input tabindex="3" type="tel" name="CustPhone" size="15" maxlength="18" /></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input tabindex="4" type="submit" name="SearchCustomers" value="' . _('Search Now') . '" />
			<input tabindex="5" type="submit" name="reset" value="' . _('Reset') .'" />
		</div>';

	if (isset($result_CustSelect)) {

		echo '<br /><table cellpadding="2" class="selection">';

		$TableHeader = '<tr>
							<th>' . _('Customer') . '</th>
							<th>' . _('Branch') . '</th>
							<th>' . _('DBA') . '</th>
							<th>' . _('Contact') . '</th>
							<th>' . _('Phone') . '</th>
							<th>' . _('Fax') . '</th>
						</tr>';
		echo $TableHeader;

		$j = 1;
		$k = 0; //row counter to determine background colour
		$LastCustomer='';
		while ($myrow=DB_fetch_array($result_CustSelect)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			if ($LastCustomer != $myrow['name']) {
				echo '<td style="font-weight:bold; font-size:18px;">' .  $myrow['name']  . '</td>';
			} else {
				echo '<td style="font-weight:bold; font-size:18px;"></td>';
			}
			echo '<td style="font-weight:bold; font-size:18px;"><input type="submit" name="Submit'.$j.'" value="' . $myrow['brname'] . '" /></td>
					<input type="hidden" name="SelectedCustomer'.$j.'" value="'. $myrow['debtorno'] . '" />
					<input type="hidden" name="SelectedBranch'.$j.'" value="' . $myrow['branchcode'] . '" />
					<td style="font-weight:bold; font-size:18px;">' . $myrow['dba']  . '</td>
					
					<td style="font-weight:bold; font-size:18px;">' . $myrow['contactname']  . '</td>
					<td style="font-weight:bold; font-size:18px;">' . $myrow['phoneno'] . '</td>
					<td style="font-weight:bold; font-size:18px;">' . $myrow['faxno'] . '</td>
					</tr>';
			$LastCustomer=$myrow['name'];
			$j++;
//end of page full new headings if
		}
//end of while loop

		echo '</table></form>';
	}//end if results to show
		}
 else 
{ /*A customer is already selected so get into the SalesCase setup proper */

	echo '<form name="SalesCaseEntry" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?salescaseref=' . $salescaseref.'" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/SalesCase.png" title="' . _('SalesCase') . '" alt="" /> ' . $_SESSION['SalesCase'.$_POST['salescaseref']]->CustomerName;

	if ($_SESSION['CompanyRecord']['currencydefault'] != $_SESSION['SalesCase'.$_POST['salescaseref']]->CurrCode){
		echo ' - ' . _('') . ' ' . $_SESSION['SalesCase'.$_POST['salescaseref']]->CurrCode . '<br />';
	}
	if ($_SESSION['ExistingSalesCase']) {
		echo  _('Modify SalesCase') . ': ' . $_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef;
	}
	echo '</p>';

	/*Set up form for entry of SalesCase header stuff */
echo '<div style = "float:left; width:60%;">';
	echo '<table class="selection">
			<tr>
				<td style="font-weight:bold; font-size:18px;">' . _('SalesCase Reference') . ':</td>
				<td style="font-weight:bold; font-size:18px;">' .$salescaseref . '
				<input type="hidden" name="salescaseref" value="'.$salescaseref.'">
				<input type="hidden" name="SelectedBranch" value="'.$_SESSION['SalesCase'.$_POST['salescaseref']]->BranchCode.'">
				<input type="hidden" name="SelectedCustomer" value="'.$_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo
				.'">
				
				</td>';
	 $sql = "select  * from 
								custcontacts inner join debtorsmaster on
								custcontacts.debtorno = debtorsmaster.debtorno 
								where debtorsmaster.debtorno 
								= '".$_SESSION['SalesCase'.$_POST['salescaseref']]->DebtorNo."'";
					$result = DB_query($sql,$db);
			
		
			
				echo '</tr><tr><td>Select Customer Contacts</td></tr>';
			
				
			echo '<td colspan="3"><select multiple required="required" name="custcontacts[]" size="5" >';
$i=0;

while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['custcontacts[]']) AND $_POST['custcontacts[]']==$myrow['contid']){
		echo '<option selected="True" value="' . $myrow['contid'] . '">'.
	'<column>'.$myrow['contactname'].'&nbsp['.
		$myrow['role'].']&nbsp('.$myrow['phoneno'].
		
		'</option>';
	} else {
		echo '<option selected="True" value="' . $myrow['contid'] . '">'.
		
		'<column>'.$myrow['contactname'].'&nbsp['.
		$myrow['role'].']&nbsp('.$myrow['phoneno'].
		
		
		')</option>'; }
}
echo '</select></td></tr>';
if (isset($_POST['updatecontacts'])){				
				$sql = "DELETE from salescasecontacts where salescaseref = '".$salescaseref."'";
				DB_query($sql,$db);
				foreach(	$_POST['custcontacts'] as $conts)
				{
					$sql = "INSERT into salescasecontacts(salescaseref,contid) 
					values ('".$salescaseref."','".$conts."')";
					DB_query($sql,$db);
					
				/*	$sql = 'SELECT * from custcontacts where contid = "'.$conts.'"';
					$result=DB_query($sql,$db);
					$myrow=DB_fetch_array($result);
					echo '<tr><td>'.$myrow['contactname'].'</td>
					<td>'.$myrow['role'].'</td><td>'.$myrow['phoneno'].'</td></tr>';
*/



				}	
}
echo '<tr><td><input type="submit" name = "updatecontacts" value="updatecontacts"></td></tr>

<tr><td><table>';
			 $sql = "SELECT * from salescasecontacts inner join custcontacts
				on salescasecontacts.contid = custcontacts.contid
						where salescaseref = '".$salescaseref."'";
				$result=DB_query($sql,$db);
				while($myrow=DB_fetch_array($result))
				{
					
					
					echo '<tr><td>'.$myrow['contactname'].'</td>
					<td>'.$myrow['role'].'</td><td>'.$myrow['phoneno'].'</td></tr>';



				}					
				echo'</td></table></tr>';






	 $sql = "select salesman.salesmanname as salesman, custbranch.brname from 
								custbranch inner join salesman on custbranch.salesman = salesman.salesmancode 
								where custbranch.branchcode = '".$_SESSION['SalesCase'.$_POST['salescaseref']]->BranchCode."'";
					$result = DB_query($sql,$db);
					$row_result = DB_fetch_array($result);
				'<tr>';
			
	//fetch from db the salesperson assigned
	
		echo 
				'<td style="font-weight:bold; font-size:18px;">' . _('Sales Person') . ':</td>
				<td style="font-weight:bold; font-size:18px;">' .$row_result['salesman'] . '
					<input type="hidden" name="salesman" value="'.$row_result['salesman'].'">
			
				</td>
				
				</tr>
				
				';
		
/*	if ($_SESSION['SalesCase'.$_POST['salescaseref']]->Status==0) {
		/*Then the SalesCase has not become an order yet and we can allow changes to the SalesCaseRef */
		//echo '<input type="text" name="SalesCaseRef" autofocus="autofocus" required="required" size="21" title="' . _('Enter the SalesCase reference. This reference will be used as the item code so no more than 20 alpha-numeric characters or underscore') . '" data-type="no-illegal-chars" maxlength="20" value="' . $_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef . '" />';
/*	} else {
		/*Just show the SalesCase Ref - dont allow modification */
/*		echo '<input type="hidden" name="SalesCaseRef" title="' . _('Enter the SalesCase reference. This reference will be used as the item code so no more than 20 alpha-numeric characters or underscore') . '" data-type="no-illegal-chars" value="' . $_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef . '" />' . $_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef;
	}
	echo '</td>
*/		
		
;

	echo'	<tr>
			<td style="font-weight:bold; font-size:18px;">' . _('SalesCase Description') . ':</td>
			<td style="font-weight:bold; font-size:18px;"><textarea name="SalesCaseDescription" style="width:100%" required="required" title="' . _('A description of the SalesCase is required') . '"  rows="4" cols="30">' . $_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseDescription . '</textarea></td>
		</tr>';
/*		if (!isset($_SESSION['SalesCase'.$_POST['salescaseref']]->commencementdate)) {
		$_SESSION['SalesCase'.$_POST['salescaseref']]->commencementdate = DateAdd(date($_SESSION['DefaultDateFormat']),'m',1);
	}
*/
		echo '<tr>
			<td style="font-weight:bold; font-size:18px;">' . _('Date of commencement') . ':</td>
			<td style="font-weight:bold; font-size:18px;">' . $_SESSION['SalesCase'.$_POST['salescaseref']]->commencementdate . '
			<input type="hidden" name="commencementdate" value="' .$_SESSION['SalesCase'.$_POST['salescaseref']]->commencementdate . '" /></td>
		</tr>';
echo'	<tr>
			<td style="font-weight:bold; font-size:18px;">' .  _('Enquiry File') . ' .pdf' . ' ' . _('format only') .':</td>
			<td style="font-weight:bold; font-size:18px;"><input type="file" id="enquiryfile" name="enquiryfile" />
			</td>
		
		';
				// begin enquiry file display
		
if ( $_SESSION['salescaseref'] && !empty($_SESSION['salescaseref'])){
	$imagefilelink = $_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.urlencode($_SESSION['salescaseref']) .urlencode($_SESSION['UsersRealName']). '.pdf';
		
		;
} else {
	if(isset($_SESSION['salescaseref']) AND  !empty($_SESSION['salescaseref']) AND file_exists($_SESSION['part_pics_dir'] . '/' .'enquiryfile_'.$_SESSION['salescaseref'].$_SESSION['UsersRealName'].urlencode(DATE('Y-m-d Hms')). '.pdf')) {
		$imagefilelink = $_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.urlencode($_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef).'*.pdf';
		if (isset($_POST['ClearImage']) ) {
		    //workaround for many variations of permission issues that could cause unlink fail
		    @unlink($imagefilelink);
		    if(is_file($imagefilelink)) {
                 prnMsg(_('You do not have access to delete this  file.'),'error');
            } else {
    		    $imagefilelink= _('No Image');
    		}
		}
		
	} else {
		$imagefilelink = _('No Image');	
	}
}

echo'</tr><tr><td style="font-weight:bold; font-size:18px;" valign="top">';
if ( $salescaseref && !empty($salescaseref)){
foreach(glob($_SESSION['part_pics_dir'] . '/' .'enquiryfile_'.$salescaseref.'*')as $fn) {
		echo '<br /><a href = "'.$RootPath.'/'.$fn.'" target = "_blank" >'.substr($fn,38).'</a>';
	}
}
	echo '</td></tr>
	<tr>
	<td style="font-weight:bold; font-size:18px;" colspan = "3">';

echo	'prospect
			<input type="text" id="salescaseprospect" name="salescaseprospect" value = "'.$_SESSION['SalesCase'.$_POST['salescaseref']]->salescaseprospects.'" />
			</tr><tr><td style="font-weight:bold; font-size:18px;">
			value <select name="salescasevalue">';
			
			if (!isset($_POST['salescasevalue'])){
	$_POST['salescasevalue'] = $_SESSION['SalesCase'.$_POST['salescaseref']]->salescasevalue;
}
if ($_SESSION['SalesCase'.$_POST['salescaseref']]->salescasevalue==''){
	echo '<option selected="selected" value="">' . _('Select Enquiry Value') . '</option>
          <option value="classA">' . _('Less than Rs.100,000') . '</option>
          <option value="classB">' . _('Between Rs 100,000 and Rs 500,000') . '</option>
          <option value="classC">' . _('Between Rs 500,000 and Rs 1000,000') . '</option>
		  <option value="classD">' . _('More than Rs.1000,000') . '</option>
          
		  ';
		  
} else if ($_SESSION['SalesCase'.$_POST['salescaseref']]->salescasevalue=='classA'){
	echo '<option value="">' . _('Select Enquiry Value') . '</option>
          <option selected="selected" value="classA">' . _('Less than Rs.100,000') . '</option>
          <option value="classB">' . _('Between Rs 100,000 and Rs 500,000') . '</option>
          <option value="classC">' . _('Between Rs 500,000 and Rs 1000,000') . '</option>
		  <option value="classD">' . _('More than Rs.1000,000') . '</option>
          
		  ';
		  }
 else if ($_SESSION['SalesCase'.$_POST['salescaseref']]->salescasevalue=='classB'){
	echo '<option value="">' . _('Select Enquiry Value') . '</option>
          <option value="classA">' . _('Less than Rs.100,000') . '</option>
          <option selected="selected" value="classB">' . _('Between Rs 100,000 and Rs 500,000') . '</option>
          <option value="classC">' . _('Between Rs 500,000 and Rs 1000,000') . '</option>
		  <option value="classD">' . _('More than Rs.1000,000') . '</option>
          
		  ';
		  } 
		else if ($_SESSION['SalesCase'.$_POST['salescaseref']]->salescasevalue=='classC'){
	echo '<option value="">' . _('Select Enquiry Value') . '</option>
          <option value="classA">' . _('Less than Rs.100,000') . '</option>
          <option value="classB">' . _('Between Rs 100,000 and Rs 500,000') . '</option>
          <option selected="selected" value="classC">' . _('Between Rs 500,000 and Rs 1000,000') . '</option>
		  <option value="classD">' . _('More than Rs.1000,000') . '</option>
          
		  ';
		  }
	else if ($_SESSION['SalesCase'.$_POST['salescaseref']]->salescasevalue=='classD'){
	echo '<option value="">' . _('Select Enquiry Value') . '</option>
          <option value="classA">' . _('Less than Rs.100,000') . '</option>
          <option value="classB">' . _('Between Rs 100,000 and Rs 500,000') . '</option>
          <option value="classC">' . _('Between Rs 500,000 and Rs 1000,000') . '</option>
		  <option selected="selected" value="classD">' . _('More than Rs.1000,000') . '</option>
          
		  ';
		  }
			
			
			
			
			
			echo'</select></td>
			<tr><td style="font-weight:bold; font-size:18px;" colspan="3"><hr></td></tr>
	
	';

//end enquiry file display

		
		
		
echo		'<tr>
			<td style="font-weight:bold; font-size:18px;">' .  _('Quotation File') . ' .pdf' . ' ' . _('format only') .':</td>
			<td style="font-weight:bold; font-size:18px;"><input type="file" id="quotationfile" name="quotationfile" /></td>
		';
				// begin quotation files display
		
if ( $_SESSION['salescaseref'] && !empty($_SESSION['salescaseref'])){
	$imagefilelink = $_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.urlencode($_SESSION['SalesCase'.$_POST['salescaseref']]->SalesCaseRef). '*.pdf';
		
		;
} else {
	if(isset($_SESSION['salescaseref']) AND  !empty($_SESSION['salescaseref']) AND file_exists($_SESSION['part_pics_dir'] . '/' .'enquiryfile_'.$_SESSION['salescaseref'].$_SESSION['UsersRealName'].urlencode(DATE('Y-m-d Hms')). '.pdf')) {
		$imagefilelink = $_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.$SelectedWO.$_SESSION['UsersRealName'].urlencode(DATE('Y-m-d Hms')). '.pdf';
		if (isset($_POST['ClearImage']) ) {
		    //workaround for many variations of permission issues that could cause unlink fail
		    @unlink($imagefilelink);
		    if(is_file($imagefilelink)) {
                 prnMsg(_('You do not have access to delete this  file.'),'error');
            } else {
    		    $imagefilelink= _('No Image');
    		}
		}
		
	} else {
		$imagefilelink = _('No Image');	
	}
}

echo'</tr><tr><td style="font-weight:bold; font-size:18px;" valign="top">';

if ( $salescaseref && !empty($salescaseref)){
foreach(glob($_SESSION['part_pics_dir'] . '/' .'quotationfile_'.$salescaseref.'*.pdf') as $fn) {
		echo '<br /><a target = "_blank" href = "'.$RootPath.'/'.$fn.'">'.substr($fn,38).'</a>';
	}
}
	echo '</td></tr>
	<tr><td style="font-weight:bold; font-size:18px;" colspan="3"><hr></td></tr>
	
	';

//end quotation files display		
		echo '<tr>
			<td style="font-weight:bold; font-size:18px;">' .  _('Purchase Order File') . ' .pdf' . ' ' . _('format only') .':</td>
			<td style="font-weight:bold; font-size:18px;"><input type="file" id="pofile" name="pofile" /></td>
		';
				// begin purchase order files display
		
if ( $_SESSION['salescaseref'] && !empty($_SESSION['salescaseref'])){
	$imagefilelink = $_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.$_SESSION['salescaseref'] .urlencode($_SESSION['UsersRealName']). '.pdf';
		
		;
} else {
	if(isset($_SESSION['salescaseref']) AND  !empty($_SESSION['salescaseref']) AND file_exists($_SESSION['part_pics_dir'] . '/' .'enquiryfile_'.$_SESSION['salescaseref'].$_SESSION['UsersRealName'].urlencode(DATE('Y-m-d Hms')). '.pdf')) {
		$imagefilelink = $_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.$SelectedWO.$_SESSION['UsersRealName'].urlencode(DATE('Y-m-d Hms')). '.pdf';
		if (isset($_POST['ClearImage']) ) {
		    //workaround for many variations of permission issues that could cause unlink fail
		    @unlink($imagefilelink);
		    if(is_file($imagefilelink)) {
                 prnMsg(_('You do not have access to delete this  file.'),'error');
            } else {
    		    $imagefilelink= _('No Image');
    		}
		}
		
	} else {
		$imagefilelink = _('No Image');	
	}
}

echo'</tr><tr><td style="font-weight:bold; font-size:18px;" valign="top">';
if ( $salescaseref && !empty($salescaseref)){
foreach(glob($_SESSION['part_pics_dir'] . '/' .'pofile_'.$salescaseref.'*.pdf') as $fn) {
		echo $fn.'<br>';
		echo '<br /><a  href = '.$RootPath.'/'.$fn.' target = "_blank">'.substr($fn,38).'</a>';
	}
}
	echo '</td></tr>
	
	<tr><td style="font-weight:bold; font-size:18px;" colspan="3"><hr></td></tr>
	
	';

//end purchase order files display		
echo		'<tr>
			<td style="font-weight:bold; font-size:18px;">' .  _('Order Confirmation File') . ' .pdf' . ' ' . _('format only') .':</td>
			<td style="font-weight:bold; font-size:18px;"><input type="file" id="ocfile" name="ocfile" /></td>
		';
						// begin order confirmation file display
		
if ( $_SESSION['salescaseref'] && !empty($_SESSION['salescaseref'])){
	$imagefilelink = $_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.$_SESSION['salescaseref'] .urlencode($_SESSION['UsersRealName']). '.pdf';
		
		;
} else {
	if(isset($_SESSION['salescaseref']) AND  !empty($_SESSION['salescaseref']) AND file_exists($_SESSION['part_pics_dir'] . '/' .'enquiryfile_'.$_SESSION['salescaseref'].$_SESSION['UsersRealName'].urlencode(DATE('Y-m-d Hms')). '.pdf')) {
		$imagefilelink = $_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.$SelectedWO.$_SESSION['UsersRealName'].urlencode(DATE('Y-m-d Hms')). '.pdf';
		if (isset($_POST['ClearImage']) ) {
		    //workaround for many variations of permission issues that could cause unlink fail
		    @unlink($imagefilelink);
		    if(is_file($imagefilelink)) {
                 prnMsg(_('You do not have access to delete this  file.'),'error');
            } else {
    		    $imagefilelink= _('No Image');
    		}
		}
		
	} else {
		$imagefilelink = _('No Image');	
	}
}

echo'</tr><tr><td style="font-weight:bold; font-size:18px;" valign="top">';

if ( $salescaseref && !empty($salescaseref)){
foreach(glob($_SESSION['part_pics_dir'] . '/' .'ocfile_'.$salescaseref.urlencode($_SESSION[UsersRealName].'.pdf')) as $fn) {
		echo '<br /><a target = "_blank" href = "'.$RootPath.'/'.$fn.'">'.substr($fn,38).'</a>';
	}
}
	echo '</td></tr>
	<tr><td style="font-weight:bold; font-size:18px;" colspan="3"><hr></td></tr>
	
	
	';

//end order confirmation file display

echo		'</table></div>';
echo'<div style = "float:right; width:40%;">
		<table >';
		echo		'<tr>
			<td style="font-weight:bold; font-size:18px;">' .  _('RPP Files') . ' .pdf' . ' ' . _('format only') .':</td>
			<td style="font-weight:bold; font-size:18px;"><input type="file" id="rppfile" name="rppfile" /></td>
		';
						// begin rpp file display
		
if ( $salescaseref && !empty($salescaseref)){
	$imagefilelink = $_SESSION['part_pics_dir'] . '/' . 'rppfile_'.$salescaseref.urlencode($_SESSION['UsersRealName']). '*.pdf';
		
		;
} else {
	if(isset($_SESSION['salescaseref']) AND  !empty($_SESSION['salescaseref']) AND file_exists($_SESSION['part_pics_dir'] . '/' .'enquiryfile_'.$_SESSION['salescaseref'].$_SESSION['UsersRealName'].urlencode(DATE('Y-m-d Hms')). '.pdf')) {
		$imagefilelink = $_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.$SelectedWO.$_SESSION['UsersRealName'].urlencode(DATE('Y-m-d Hms')). '.pdf';
		if (isset($_POST['ClearImage']) ) {
		    //workaround for many variations of permission issues that could cause unlink fail
		    @unlink($imagefilelink);
		    if(is_file($imagefilelink)) {
                 prnMsg(_('You do not have access to delete this  file.'),'error');
            } else {
    		    $imagefilelink= _('No Image');
    		}
		}
		
	} else {
		$imagefilelink = _('No Image');	
	}
}

echo'</tr><tr><td colspan="2" style="font-weight:bold; font-size:18px;" valign="top">';
//echo 'rppfile_'.$salescaseref.urlencode($_SESSION[UsersRealName]).'*.pdf';
if ( $salescaseref && !empty($salescaseref)){
foreach(glob($_SESSION['part_pics_dir'] . '/' .'rppfile_'.$salescaseref.'*.pdf') as $fn) {
		echo '<br /><a target = "_blank" href = "'.$fn.'">'.substr($fn,38).'</a>';
	}
}
	echo '</td></tr>
	<tr><td style="font-weight:bold; font-size:18px;" colspan="3"><hr></td></tr>
	
	
	';

//end RPP file display
		

		echo'</table></div>';

	$sql = 'SELECT * from dc where salescaseref="'.$salescaseref.'"';
		$result = DB_query($sql,$db);
echo'		<div style = "clear:both;" class="selection">
		<a href = "'.$RootPath.'/dc.php?New=Yes&salescaseref='.$salescaseref.'" target = "_blank"><h2>Make Delivery Challan</h2></a>
		<table>
		<table><tr><td style="font-weight:bold; font-size:18px;">Uploaded DCs</td><td style="font-weight:bold; font-size:18px;">Uploaded Courier Slips</td><td style="font-weight:bold; font-size:18px;">Uploaded Invoices</td>
		<td style="font-weight:bold; font-size:18px;">Uploaded GRBs</td>
		</tr></td>

		</tr>
		
		';
			while ($row_result=DB_fetch_array($result))
			{
				$_SESSION['maxdispatch'] = $row_result['dispatchid'];
				if(!isset($row_result['courierslipdate']) OR $row_result['courierslipdate'] == "0000-00-00 00:00:00") 
						$row_result['courierslipdate']='';
						if(!isset($row_result['grbdatedate']) OR $row_result['grbdate'] == "0000-00-00 00:00:00") 
						$row_result['grbdate']='';
			
			if ($row_result['dcstatus'] == ''){	
				
				echo '</tr><td><a href="'.$RootPath.'/PDFDC.php?RequestNo=' . $row_result['dispatchid'] . '&salescaseref=' . $salescaseref . '">' .$row_result['despatchdate']. '</a></td>
<td><a target="_blank" href="'.$_SESSION['part_pics_dir'] . '/' . 'Courierslip_'.$row_result['dispatchid']. '.pdf">' .$row_result['courierslipdate']. '</a></td>
<td><input type = "checkbox" name = "sendinvoice' .$row_result['dispatchid']. 
					'" value = "sent to accounts for invoice"></td>
<td><a target="_blank" href="'.$_SESSION['part_pics_dir'] . '/' . 'GRB_'.$row_result['dispatchid']. '.pdf">' .$row_result['grbdate']. '</a></td></tr>';
				
			}
					
			if ($row_result['dcstatus'] == 'sent to accounts for invoice')
			{
				
				echo '<tr><td><a href="'.$RootPath.'/PDFDC.php?RequestNo=' . $row_result['dispatchid'] . '">' .$row_result['despatchdate']. '</a></td>
<td><a target="_blank" href="'.$_SESSION['part_pics_dir'] . '/' . 'Courierslip_'.$row_result['dispatchid']. '.pdf">' .$row_result['courierslipdate']. '</a></td>
<td><b>sent to accounts for invoice</b></td>
<td><a target="_blank" href="'.$_SESSION['part_pics_dir'] . '/' . 'GRB_'.$row_result['dispatchid']. '.pdf">' .$row_result['grbdate']. '</a></td></tr>';
			
				
			}
				if ($row_result['dcstatus'] == 'DC Invoiced')
			{
				
				echo '<tr><td><a href="'.$RootPath.'/PDFDC.php?RequestNo=' . $row_result['dispatchid'] . '">' .$row_result['despatchdate']. '</a></td>
<td><a target="_blank" href="'.$_SESSION['part_pics_dir'] . '/' . 'Courierslip_'.$row_result['dispatchid']. '.pdf">' .$row_result['courierslipdate']. '</a></td>
<td><a target="_blank" href="'.$_SESSION['part_pics_dir'] . '/' . 'Invoice_'.$row_result['dispatchid']. '.pdf">' .$row_result['invoicedate']. '</a></td>
<td><a target="_blank" href="'.$_SESSION['part_pics_dir'] . '/' . 'GRB_'.$row_result['dispatchid']. '.pdf">' .$row_result['grbdate']. '</a></td></tr>';
			
				
			}

			}
			
		echo '</table>
		</div>
		
		';

	
	
	
	

		echo '<div class="centre">
				 <br />
				 <input type="submit" name="submitsalescase" value="' . _('Submit') . '" />
			  </div>';
	
	
	echo '</form>';
			$sql = "select * from salescasecomments where salescaseref = '".$salescaseref."'";
$result = DB_query($sql,$db);

echo '<form name="SalesCaseComments" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?salescaseref=' . $salescaseref.'" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<center><h2>Comments</h2></center>';
echo'	<table  class="selection">';

while ($row = DB_fetch_array($result))
{
	echo '<tr><td style="font-weight:bold; font-size:18px;">'.$row['username'].' '.$row['time'].'</td></tr><tr><td>'.$row['comment'].'</tr>';
}


echo '<tr>


<td style="font-weight:bold; font-size:18px;"><textarea name = "comment" rolws="10" cols="150"></textarea></td>

</tr>

<tr>


<td style="font-weight:bold; font-size:18px;" align="center"><input type = "submit" name = "SubmitComment" value = "submit"></textarea><td style="font-weight:bold; font-size:18px;">

</tr>
</table>

</form>
';

} /*end of if customer selected  and entering SalesCase header*/

		
include('includes/footer.inc');
?>
