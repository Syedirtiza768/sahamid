<?php

/* $Id: StockLocStatus.php 6033 2013-06-24 07:36:26Z daintree $*/

include('includes/session.inc');

$Title = _('Reprint Documents');

include('includes/header.inc');

$_SESSION['ToDate'] = $_POST['ToDate'];
$_SESSION['FromDate'] = $_POST['FromDate'];
$_SESSION['StockLocation'] = $_POST['StockLocation'];
$_SESSION['User'] = $_POST['User'];
$_SESSION['document'] = $_POST['document'];
for ($i=0; $i<=$_SESSION['maxdispatch']; $i++)

{
	if (isset ($_POST['sendinvoice'.$i]))
	{
		$sql = 'update dc set dcstatus = "'.$_POST['sendinvoice'.$i].'" where dispatchid = '.$i.'';
		DB_query($sql, $db);
	}
	if (isset ($_POST['invoicesent'.$i]))
	{
		$sql = 'update dc set dcstatus = "'.$_POST['invoicesent'.$i].'" where dispatchid = '.$i.'';
		DB_query($sql, $db);
	}
	if (isset ($_POST['dccomplete'.$i]))
	{
		$sql = 'update dc set dccomplete = "'.$_POST['dccomplete'.$i].'" where dispatchid = '.$i.'';
		DB_query($sql, $db);
	}
}
for ($i=0; $i<=$_SESSION['maxdispatch']; $i++)
{
	
	
	$path = $_FILES['PurchaseOrder'.$i]['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION);
if (isset($_FILES['PurchaseOrder'.$i]) AND $_FILES['PurchaseOrder'.$i]['name'] !='') {

	$result	= $_FILES['PurchaseOrder'.$i]['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . 'PurchaseOrder_'.$i.'.'. $ext;

	 //But check for the worst
	if ( $_FILES['PurchaseOrder'.$i]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['PurchaseOrder'.$i]['error'] == 6 ) {  //upload temp directory check
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
		$result  =  move_uploaded_file($_FILES['PurchaseOrder'.$i]['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');

	
	}
}
if (isset($_POST['ClearImagePurchaseOrder' .$i]) ) {
		echo "namely";
		   //workaround for many variations of permission issues that could cause unlink fail
		   @unlink($_SESSION['part_pics_dir'] . '/' .'PurchaseOrder_'.$i.'.pdf');
		    if(is_file($_SESSION['part_pics_dir'] . '/' .'PurchaseOrder_'.$myrow['dispatchid'].'.pdf')) {
              //   prnMsg(_('You do not have access to delete this item image file.'),'error');
            } else {
    		    $StockImgLink = _('No Image');
    		}
		}
}
//-------------------------------------------------------------------

for ($i=0; $i<=$_SESSION['maxdispatch']; $i++)
{
	
	
	$path = $_FILES['CourierSlip'.$i]['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION);
if (isset($_FILES['CourierSlip'.$i]) AND $_FILES['CourierSlip'.$i]['name'] !='') {

	$result	= $_FILES['CourierSlip'.$i]['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . 'CourierSlip_'.$i.'.'. $ext;

	
	 //But check for the worst
	if ( $_FILES['CourierSlip'.$i]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['CourierSlip'.$i]['error'] == 6 ) {  //upload temp directory check
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
		$result  =  move_uploaded_file($_FILES['CourierSlip'.$i]['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
		$courierslipdate = date('y-m-d h:i:s');
		$sql = "UPDATE dc
						SET 
						
							courierslipdate='" .$courierslipdate. "'
							
					WHERE dispatchid='".$i."'";
	
	
	DB_query($sql,$db);
	
	
	
	}
}
if (isset($_POST['ClearImageCourierSlip' .$i]) ) {
			
		   //workaround for many variations of permission issues that could cause unlink fail
		    @unlink($_SESSION['part_pics_dir'] . '/' .'CourierSlip_'.$i.'.pdf');
		    if(is_file($_SESSION['part_pics_dir'] . '/' .'CourierSlip_'.$i.'.pdf')) {
                 prnMsg(_('You do not have access to delete this item image file.'),'error');
            } else {
    		    $StockImgLink = _('No Image');
    		}
		}
}
//-------------------------------------------------------------------


for ($i=0; $i<=$_SESSION['maxdispatch']; $i++)
{
	
	
	$path = $_FILES['Invoice'.$i]['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION);
if (isset($_FILES['Invoice'.$i]) AND $_FILES['Invoice'.$i]['name'] !='') {

	$result	= $_FILES['Invoice'.$i]['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . 'Invoice_'.$i.'.'. $ext;

	 //But check for the worst
	if ( $_FILES['Invoice'.$i]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['Invoice'.$i]['error'] == 6 ) {  //upload temp directory check
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
		$result  =  move_uploaded_file($_FILES['Invoice'.$i]['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
		$invoicedate = date('y-m-d h:i:s');
		$sql = "UPDATE dc
						SET 
							dcstatus='DC Invoiced',
							invoicedate='" .$invoicedate. "'
							
					WHERE dispatchid='".$i."'";
	
	
	DB_query($sql,$db);
	
	
	}
}
if (isset($_POST['ClearImageInvoice' .$i]) ) {
		
		   //workaround for many variations of permission issues that could cause unlink fail
		    @unlink($_SESSION['part_pics_dir'] . '/' .'Invoice_'.$i.'.pdf');
		    if(is_file($_SESSION['part_pics_dir'] . '/' .'Invoice_'.$i.'.pdf')) {
                prnMsg(_('You do not have access to delete this item image file.'),'error');
            } else {
    		    $StockImgLink = _('No Image');
    		}
		}
}
//-------------------------------------------------------------------


for ($i=0; $i<=$_SESSION['maxdispatch']; $i++)
{
	
	
	$path = $_FILES['GRB'.$i]['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION);
if (isset($_FILES['GRB'.$i]) AND $_FILES['GRB'.$i]['name'] !='') {

	$result	= $_FILES['GRB'.$i]['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . 'GRB_'.$i.'.'. $ext;

	 //But check for the worst
	if ( $_FILES['GRB'.$i]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['GRB'.$i]['error'] == 6 ) {  //upload temp directory check
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
		$result  =  move_uploaded_file($_FILES['GRB'.$i]['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
	$grbdate = date('y-m-d h:i:s');
		$sql = "UPDATE dc
						SET 
						
							grbdate='" .$grbdate. "'
							
					WHERE dispatchid='".$i."'";
	
	
	DB_query($sql,$db);
	
	
	
	}
}
if (isset($_POST['ClearImageGRB' .$i]) ) {
			//workaround for many variations of permission issues that could cause unlink fail
		    @unlink($_SESSION['part_pics_dir'] . '/' .'GRB_'.$i.'.pdf');
		    if(is_file($_SESSION['part_pics_dir'] . '/' .'GRB_'.$i.'.pdf')) {
              //   prnMsg(_('You do not have access to delete this item image file.'),'error');
            } else {
    		    $StockImgLink = _('No Image');
    		}
		}
}
//-------------------------------------------------------------------




if (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}
if (isset($_POST['substore']))
$_SESSION['substore'] = $_POST['substore'];
//$_POST['substore'] = $_SESSION['substore'];
if (isset($_POST['user']))
$_SESSION['user'] = $_POST['user'];
$_POST['user'] = $_SESSION['user'];

echo '<form name = "searchform" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="POST">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$sql = "SELECT loccode,
    	       locationname
    	FROM locations";
$resultStkLocs = DB_query($sql,$db);
 $sql = "SELECT substoreid,
    	       description
    	FROM substores where locid = '".$_POST['StockLocation']."'";
$resultSubstores = DB_query($sql,$db);

 $sql = "SELECT realname
    	FROM www_users where defaultlocation like '%".$_POST['StockLocation']."%'";
$resultUsers = DB_query($sql,$db);

if(isset($_POST['UpdateCategories']))
{

if ($_POST['StockCat'] == 'All')
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
		and stockcategory.categoryid = "."'".$_POST['StockCat']."'"."
		ORDER BY manufacturers_name";
}
		$result2 = DB_query($SQL, $db);

}

else
{
	$SQL = "select * from manufacturers";
$result2 = DB_query($SQL, $db);
}
echo '<p class="page_title_text">
         <img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
      </p>';

echo '<table class="selection">';

echo '<tr><td>' . _('Select Document To be Reprinted') . ':</td>
		<td><select name="document">';
if (!isset($_POST['document'])){
	$_SESSION['document']='All';
}
if ($_SESSION['document']=='All'){
	echo '<option selected="selected" value="All">' . _('All') . '</option>
          <option value="IGP">' . _('Inwards Gate Pass') . '</option>
          <option value="OGP">' . _('Outwards Gate Pass') . '</option>
          <option value="DC">' . _('Delivery Challan') . '</option>';
} else if ($_SESSION['document']=='IGP'){
	echo '<option value="All">' . _('All') . '</option>
          <option value="IGP" selected="selected">' . _('Inwards Gate Pass') . '</option>
          <option value="OGP">' . _('Outwards Gate Pass') . '</option>
          <option value="DC">' . _('Delivery Challan') . '</option>';
		  }
 else if ($_SESSION['document']=='OGP'){
	echo '<option value="All">' . _('All') . '</option>
          <option value="IGP">' . _('Inwards Gate Pass') . '</option>
          <option value="OGP" selected = "selected">' . _('Outwards Gate Pass') . '</option>
          <option value="DC">' . _('Delivery Challan') . '</option>';
		  } else  if ($_SESSION['document']=='DC'){
	echo '<option value="All">' . _('All') . '</option>
          <option value="IGP">' . _('Inwards Gate Pass') . '</option>
          <option value="OGP" >' . _('Outwards Gate Pass') . '</option>
          <option value="DC" selected = "selected">' . _('Delivery Challan') . '</option>';
		  }

echo '</select></td></tr>';
if ($_SESSION['AccessLevel'] != 14)
{
echo		'<tr>
			<td>' . _('From Stock Location') . ':</td>
			<td><select name="StockLocation" onchange=" ReloadForm(searchform.UpdateCategories)"> ';
			if (!isset($_POST['StockLocation'])){
	$_POST['StockLocation']='';
}
if ($_POST['StockLocation']==''){
	echo '<option selected="selected" value="">' . _('All') . '</option>';
} else {
	echo '<option value="">' . _('All') . '</option>';
}
		
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_SESSION['StockLocation']) AND $_SESSION['StockLocation']!=""){
		if ($myrow['loccode'] == $_SESSION['StockLocation']){
		     echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
		
	}
	elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
}
echo '</select></td>
	</tr>';
}

else echo '<input type = "hidden" name = "StockLocation" value = "'.$_SESSION['UserStockLocation'].'">';
	/*
echo'			<tr>
			<td>' . _('From Sub Store') . ':</td>
			<td><select name="substore" onchange=" ReloadForm(searchform.UpdateCategories)"> ';
			if (!isset($_POST['substore'])){
	$_POST['substore']='';
}
if ($_POST['substore']==''){
	echo '<option selected="selected" value="">' . _('All') . '</option>';
} else {
	echo '<option value="">' . _('All') . '</option>';
}
		
while ($myrow=DB_fetch_array($resultSubstores)){
	
	if (isset($_POST['substore']) AND $_POST['substore']!=""){
		if ($myrow['substoreid'] == $_POST['substore']){
		     echo '<option selected="selected" value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
		}
	} 
	 elseif ($myrow['substoreid']==$_SESSION['substore']){
		 echo '<option selected="selected" value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
		 $_POST['substore']=$myrow['substoreid'];
	} else {
		 echo '<option value="' . $myrow['substoreid'] . '">' . $myrow['description'] . '</option>';
	}
	
	
}
echo '</select></td>
	</tr>';
	*/
	if ($_SESSION['AccessLevel'] != 14)
	{
echo'			<tr>
			<td>' . _('User') . ':</td>
			<td><select name="user" onchange=" ReloadForm(searchform.UpdateCategories)"> ';
			if (!isset($_POST['user'])){
	$_POST['user']='';
}
if ($_POST['user']==''){
	echo '<option selected="selected" value="">' . _('All') . '</option>';
} else {
	echo '<option value="">' . _('All') . '</option>';
}
		
while ($myrow=DB_fetch_array($resultUsers)){
	
	if (isset($_SESSION['user']) AND $_SESSION['user']!=""){
		if ($myrow['realname'] == $_SESSION['user']){
		     echo '<option selected="selected" value="' . $myrow['realname'] . '">' . $myrow['realname'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['realname'] . '">' . $myrow['realname'] . '</option>';
		}
	} 
	 elseif ($myrow['realname']==$_SESSION['user']){
		 echo '<option selected="selected" value="' . $myrow['realname'] . '">' . $myrow['realname'] . '</option>';
		 $_POST['user']=$myrow['realname'];
	} else {
		 echo '<option value="' . $myrow['realname'] . '">' . $myrow['realname'] . '</option>';
	}
	
	
}
echo '</select></td>
	</tr>';
	}
else 
echo '<input type = "hidden" name = "user" value = "'.$_SESSION['UsersRealName'].'" >';	
echo	'<tr>
			<td>' . _('Enter the date from which the transactions are to be listed') . ':</td>';
			if ($_SESSION['FromDate'])
		echo '<td><input type="text" required="required" name="FromDate" maxlength="10" size="10" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . $_SESSION['FromDate'] . '" /></td>';
			
		else
		echo '<td><input type="text" required="required" name="FromDate" maxlength="10" size="10" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '" /></td>';
			
		echo '<tr>
			<td>' . _('Enter the date to which the transactions are to be listed') . ':</td>
			';
			if ($_SESSION['ToDate'])
		echo '<td><input type="text" required="required" name="ToDate" maxlength="10" size="10" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . $_SESSION['ToDate'] . '" /></td>';
		
		else
		echo '<td><input type="text" required="required" name="ToDate" maxlength="10" size="10" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '" /></td>';
		
		echo '</tr>
		
	
	
	
	';



$SQL="SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$result1 = DB_query($SQL,$db);
if (DB_num_rows($result1)==0){
	echo '</table><p>';
	prnMsg(_('There are no stock categories currently defined please use the link below to set them up'),'warn');
	echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
	include ('includes/footer.inc');
	exit;
}
/*
echo '<tr><td>' . _('In Stock Category') . ':</td>
		<td><select name="StockCat" onchange="ReloadForm(searchform.UpdateCategories)">';
if (!isset($_POST['StockCat'])){
	$_POST['StockCat']='';
}
if ($_POST['StockCat']==''){
	echo '<option selected="selected" value="">' . _('All') . '</option>';
} else {
	echo '<option value="">' . _('All') . '</option>';
}
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['categoryid']==$_POST['StockCat']){
		echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	}
}

echo '</select></td></tr>';
echo '<tr><td>' . _('Shown Only Items Where') . ':</td>
		<td><select name="BelowReorderQuantity">';
if (!isset($_POST['BelowReorderQuantity'])){
	$_POST['BelowReorderQuantity']='All';
}
if ($_POST['BelowReorderQuantity']=='All'){
	echo '<option selected="selected" value="All">' . _('All') . '</option>
          <option value="Below">' . _('Only items below re-order quantity') . '</option>
          <option value="NotZero">' . _('Only items where stock is available') . '</option>
          <option value="OnOrder">' . _('Only items currently on order') . '</option>';
} else if ($_POST['BelowReorderQuantity']=='Below') {
	echo '<option value="All">' . _('All') . '</option>
          <option selected="selected" value="Below">' . _('Only items below re-order quantity') . '</option>
          <option value="NotZero">' . _('Only items where stock is available') . '</option>
          <option value="OnOrder">' . _('Only items currently on order') . '</option>';
} else if ($_POST['BelowReorderQuantity']=='OnOrder') {
    echo '<option value="All">' . _('All') . '</option>
          <option value="Below">' . _('Only items below re-order quantity') . '</option>
          <option value="NotZero">' . _('Only items where stock is available') . '</option>
          <option selected="selected" value="OnOrder">' . _('Only items currently on order') . '</option>';
} else  {
	echo '<option value="All">' . _('All') . '</option>
          <option value="Below">' . _('Only items below re-order quantity') . '</option>
          <option selected="selected" value="NotZero">' . _('Only items where stock is available') . '</option>
          <option value="OnOrder">' . _('Only items currently on order') . '</option>';
}

echo '</select></td></tr>';
echo '<tr>';
echo '<td>' . _('Brand') . ':';
echo '</td><td>';
echo '<select name="brand">';
if (!isset($_POST['brand'])) {
	$_POST['brand'] ='';
}

if ($_POST['brand'] == '') {
	echo '<option selected="selected" value="">' . _('All') . '</option>';
} else {
	echo '<option value="">' . _('All') . '</option>';
}

while ($myrow2 = DB_fetch_array($result2)) {
	if ($myrow2['manufacturers_id'] == $_POST['brand']) {
		echo '<option selected="selected" value="' . $myrow1['manufacturers_id'] . '">' . $myrow2['manufacturers_name'] . '</option>';
	} else {
		echo '<option value="' . $myrow2['manufacturers_id'] . '">' . $myrow2['manufacturers_name'] . '</option>';
	}
}
echo '</select></td>';



echo '</tr>
*/  
 echo '</table>';
echo '<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '" />';

echo '<br />
     <div class="centre">
	 
          <input type="submit" name="reprintdocuments" value="' . _('Reprint Documents') . '" />
     </div>
	 '
	 ;

if (isset($_POST['reprintdocuments'])){
if ($_POST['document'] == 'IGP')
{
		
		$sql = "SELECT igp.dispatchid,
		igp.loccode,
		locations.locationname,
		igp.despatchdate,
		igp.storemanager,
		igp.receivedfrom
		from igp inner join locations on igp.loccode 
		= locations.loccode
		where
		igp.loccode like '%".$_SESSION['StockLocation']."%'
		and(
		igp.storemanager like '%".$_SESSION['user']."%'
		or
		igp.receivedfrom like '%".$_SESSION['user']."%'
		or igp.receivedfrom not in (select realname from www_users)
		)
			AND date_format(igp.despatchdate, '%Y-%m-%d')>='".FormatDateForSQL($_SESSION['FromDate'])."'
			AND date_format(igp.despatchdate, '%Y-%m-%d')<='".FormatDateForSQL($_SESSION['ToDate'])."'
		
		";
	$ErrMsg =  _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');
	$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	echo '<br />
	
         <table cellpadding="5" cellspacing="4" class="selection">';

	$TableHeader = '<tr>
    					<th>' . _('Document ID') . '</th>
						<th>' . _('Location') . '</th>
						<th>' . _('Date') . '</th>
						<th>' . _('Store Manager') . '</th>
						<th>' . _('Received From') . '</th>';
						
					echo'</tr>';
	echo $TableHeader;
	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($Result)) {

		$StockID = $myrow['stockid'];

		$linesql = "";
	//$DemandResult = DB_query($linesql,$db,$ErrMsg);
		if ($k==1){
					echo '<tr class="OddTableRows">';
					$k=0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k=1;
				}
			echo '<td><a href = "PDFIGP.php?RequestNo=' .$myrow['dispatchid']. '">
			'.$myrow['dispatchid'].'</a>
			</td>
						<td>' .$myrow['locationname'] . '</td>
						<td>' .$myrow['despatchdate']. '</td>
						<td>' .$myrow['storemanager']. '</td>
						<td>' .$myrow['receivedfrom']. '</td>
    					<td>'; 
	


						
						
						
						
						
						
						
						echo '</td>
					</tr>';
		
		
		
}	

}
if ($_POST['document'] == 'OGP')
{
		
		$sql = "SELECT posdispatch.dispatchid,
		posdispatch.loccode,
		locations.locationname,
		posdispatch.despatchdate,
		posdispatch.storemanager,
		posdispatch.deliveredto
		from posdispatch inner join locations on posdispatch.loccode 
		= locations.loccode
		where
		posdispatch.loccode like '%".$_SESSION['StockLocation']."%'
		and(
		posdispatch.storemanager like '%".$_SESSION['user']."%'
		or
		posdispatch.deliveredto like '%".$_SESSION['user']."%'
		
		)
			AND date_format(posdispatch.despatchdate, '%Y-%m-%d')>='".FormatDateForSQL($_SESSION['FromDate'])."'
			AND date_format(posdispatch.despatchdate, '%Y-%m-%d')<='".FormatDateForSQL($_SESSION['ToDate'])."'
		
		";
		
	$ErrMsg =  _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	echo '<br />
         <table cellpadding="5" cellspacing="4" class="selection">';

	$TableHeader = '<tr>
    					<th>' . _('Document ID') . '</th>
						<th>' . _('Location') . '</th>
						<th>' . _('Date') . '</th>
						<th>' . _('Store Manager') . '</th>
						<th>' . _('Delivered To') . '</th>
    					
					</tr>';
	echo $TableHeader;
	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($Result)) {

		$StockID = $myrow['stockid'];

		$linesql = "";
//	$DemandResult = DB_query($linesql,$db,$ErrMsg);
		if ($k==1){
					echo '<tr class="OddTableRows">';
					$k=0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k=1;
				}
			echo '<td><a href = "PDFOGP.php?RequestNo=' .$myrow['dispatchid']. '">
			'.$myrow['dispatchid'].'</a>
			</td>
						<td>' .$myrow['locationname'] . '</td>
						<td>' .$myrow['despatchdate']. '</td>
						<td>' .$myrow['storemanager']. '</td>
						<td>' .$myrow['deliveredto']. '</td>
    					
					</tr>';
		
		
		
}	
}
if ($_POST['document'] == 'DC')
{
		
		$sql = "SELECT dc.dispatchid,
		dc.loccode,
		locations.locationname,
		dc.despatchdate,
		dc.dcstatus,
		dc.dccomplete,
		dc.dccomplete,
		dc.dba,
		dc.deliverto,
		dc.salesperson,
		dc.salescaseref
		from dc inner join locations on dc.loccode 
		= locations.loccode
		where
		dc.loccode like '%".$_SESSION['StockLocation']."%'
		and(
		dc.salesperson like '%".$_SESSION['user']."%'
		
		)
			AND date_format(dc.despatchdate, '%Y-%m-%d')>='".FormatDateForSQL($_SESSION['FromDate'])."'
			AND date_format(dc.despatchdate, '%Y-%m-%d')<='".FormatDateForSQL($_SESSION['ToDate'])."'
		
		";
		
	$ErrMsg =  _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');
	$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	echo '<br />';
	    $TableHeader = '<tr>
    					<th>' . _('Document ID') . '</th>
						<th>' . _('For Client') . '</th>';
						if ($_SESSION['AccessLevel'] == 10 || $_SESSION['AccessLevel']  == 22 )
						{		
		$TableHeader.=	'<th>' . _('Edit') . '</th>';
						}
		$TableHeader.= '<th>' . _('Location') . '</th>
						<th>' . _('Date') . '</th>
						<th>' . _('Store Manager') . '</th>
						<th>' . _('Deliver To') . '</th>';
						
				if ($_SESSION['AccessLevel'] == 14||$_SESSION['AccessLevel'] == 22 || $_SESSION['AccessLevel'] == 23)
						{		
		$TableHeader.=	'<th>' . _('Send for Invoice') . '</th>';
						}
				if ($_SESSION['AccessLevel'] != 14||$_SESSION['AccessLevel'] != 22 || $_SESSION['AccessLevel'] != 23)
				{
		$TableHeader.=	'<th>' . _('Invoice DC') . '</th>';
				}
				
		$TableHeader.=	'<th>' . _('Complete Delivery') . '</th>';
				
						
						
		$TableHeader.=	'</tr>';
	$TableHeader2=	'<tr>
					<th>' . _('Upload Purchase Order') . '</th>
						<th>' . _('Upload Courier Slip') . '</th>
    					<th>' . _('Upload Invoice') . '</th>
						<th>' . _('Upload GRB') . '</th>
						<th>' . _('Submit') . '</th>
						</tr>
					';
	
	
	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($Result)) {
		$_SESSION['maxdispatch'] = $myrow['dispatchid'];
 echo  '<table cellpadding="5" cellspacing="4" class="selection">';

		$StockID = $myrow['stockid'];

		$linesql = "";
	//$DemandResult = DB_query($linesql,$db,$ErrMsg);
		if ($k==1){
					echo '<tr class="OddTableRows">';
					$k=0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k=1;
				}
				echo $TableHeader;
				
			echo '<td><a href = "PDFDC.php?RequestNo=' .$myrow['dispatchid']. '&salescaseref=' .$myrow['salescaseref'] . '">
			'.$myrow['dispatchid'].'</a>
			</td><td><a href = "PDFDCCLIENT.php?RequestNo=' .$myrow['dispatchid']. '">
			'.$myrow['dispatchid'].'</a>
			</td>';	
			if ($_SESSION['AccessLevel'] == 10 || $_SESSION['AccessLevel'] == 22)
						{		
		echo '<td><a href = "dcclient.php?tranno=' .$myrow['dispatchid']. '">
			'.$myrow['dispatchid'].'</a>
			</td>';
						}
			echo '<td>' .$myrow['locationname'] . '</td>
						<td>' .$myrow['despatchdate']. '</td>
						<td>' .$myrow['salesperson']. '</td>
						<td>' .$myrow['deliverto']. '</td>';
						
						if (($_SESSION['AccessLevel'] == 14 || $_SESSION['AccessLevel'] == 22 || $_SESSION['AccessLevel'] == 23) and $myrow['dcstatus'] == '')
						{
					echo	'<td><input type = "checkbox" name = "sendinvoice' .$myrow['dispatchid']. 
					'" value = "sent to accounts for invoice"></td>';
						}
					elseif($_SESSION['AccessLevel'] == 14 || $_SESSION['AccessLevel'] == 22 || $_SESSION['AccessLevel'] == 23){	
					echo	'<td style = "color:red">' .$myrow['dcstatus']. '</td>';
					}
						if ($myrow['dcstatus'] == 'sent to accounts for invoice')
						{
					echo	'<td style = "color:red">Invoice this dc</td>';
						}
					elseif($_SESSION['AccessLevel'] != 14 || $_SESSION['AccessLevel'] != 22 || $_SESSION['AccessLevel'] != 23){	
					echo	'<td style = "color:red">' .$myrow['dcstatus']. '</td>';
					}
					if (($_SESSION['AccessLevel'] == 14 || $_SESSION['AccessLevel'] == 22 || $_SESSION['AccessLevel'] == 23) and $myrow['dccomplete'] == '')
						{
					echo	'<td><input type = "checkbox" name = "dccomplete' .$myrow['dispatchid']. '" value = "Delivery Completed"></td>';
						}
					else{	
					echo	'<td style = "color:red">' .$myrow['dccomplete']. '</td>';
					}
    				echo	'</tr>
						</table>
						   <table cellpadding="5" cellspacing="4" class="selection">';

						;
						
						echo $TableHeader2;
					//po upload begin
					echo '<tr>
		
		<td><input type="file" id="PurchaseOrder" name="PurchaseOrder' .$myrow['dispatchid']. '" />
		 
		<br />';
		
		if($_SESSION['AccessLevel'] == 10 || $_SESSION['AccessLevel'] == 23 || $_SESSION['AccessLevel'] == 22)
		echo '<input type="checkbox" name="ClearImagePurchaseOrder' .$myrow['dispatchid']. '" id="ClearImagePurchaseOrder' .$myrow['dispatchid']. '" value="1" > '._('Clear Image').'';
	
$path = $_FILES['PurchaseOrder'.$i]['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION);

if (function_exists('imagecreatefromjpg')){
	$StockImgLink = '<img src="PurchaseOrderSTStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
		'&amp;StockID='.urlencode('PurchaseOrder_'.$myrow['dispatchid']).
		'&amp;text='.
		'&amp;width=100'.
		'&amp;height=100'.
		'" alt="" />';
} else {
	if( file_exists($_SESSION['part_pics_dir'] . '/' .'PurchaseOrder_'.$myrow['dispatchid'].'.pdf') ) {
		$StockImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/' . 'PurchaseOrder_'.$myrow['dispatchid'] . '.'.$ext.'" height="100" width="100" />';
		
	} else {
		$StockImgLink = _('No Image');	
	}
}


	if ($StockImgLink!=_('No Image')){
	echo '' . _('Image') . '<br /><a target = "_blank" href = "'.$_SESSION['part_pics_dir'] . '/' .'PurchaseOrder_'.$myrow['dispatchid'].'.pdf'.'">download</a></td>';
	}
	
		//po upload end
			//courierslip upload begin
					echo '
		
		<td><input type="file" id="CourierSlip" name="CourierSlip' .$myrow['dispatchid']. '" />
		 ';
		
		if($_SESSION['AccessLevel'] == 10||$_SESSION['AccessLevel'] == 23 || $_SESSION['AccessLevel'] == 22)
		echo '<br /><input type="checkbox" name="ClearImageCourierSlip' .$myrow['dispatchid']. '" id="ClearImageCourierSlip' .$myrow['dispatchid']. '" value="1" > '._('ClearImage').'';
$path = $_FILES['CourierSlip'.$i]['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION);

if (function_exists('imagecreatefromjpg')){
	$StockImgLink = '<img src="CourierSlipSTStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
		'&amp;StockID='.urlencode('CourierSlip_'.$myrow['dispatchid']).
		'&amp;text='.
		'&amp;width=100'.
		'&amp;height=100'.
		'" alt="" />';
} else {
	if( file_exists($_SESSION['part_pics_dir'] . '/' .'CourierSlip_'.$myrow['dispatchid'].'.pdf') ) {
		$StockImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/' . 'CourierSlip_'.$myrow['dispatchid'] . '.'.$ext.'" height="100" width="100" />';
		
	} else {
		$StockImgLink = _('No Image');	
	}
}


	if ($StockImgLink!=_('No Image')){
	echo '' . _('Image') . '<br /><a target = "_blank" href = "'.$_SESSION['part_pics_dir'] . '/' .'CourierSlip_'.$myrow['dispatchid'].'.pdf'.'">download</a></td>';
	}
	
		// upload CourierSlip end
	
		
			//invoice upload begin
			if($myrow['dcstatus'] == 'sent to accounts for invoice')
			{
			echo '
		
		<td><input type="file" id="Invoice" name="Invoice' .$myrow['dispatchid']. '" />
		 ';
		}
			else
			{
				
				echo "<td>";
			}
		if($_SESSION['AccessLevel'] == 10 || $_SESSION['AccessLevel'] == 23 || $_SESSION['AccessLevel'] == 22)
		echo '<br /><input type="checkbox" name="ClearImageInvoice' .$myrow['dispatchid']. '" id="ClearImageInvoice' .$myrow['dispatchid']. '" value="1" > '._('Clear Image').'';
$path = $_FILES['Invoice'.$i]['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION);

if (function_exists('imagecreatefromjpg')){
	$StockImgLink = '<img src="InvoiceSTStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
		'&amp;StockID='.urlencode('Invoice_'.$myrow['dispatchid']).
		'&amp;text='.
		'&amp;width=100'.
		'&amp;height=100'.
		'" alt="" />';
} else {
	if( file_exists($_SESSION['part_pics_dir'] . '/' .'Invoice_'.$myrow['dispatchid'].'.pdf') ) {
		$StockImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/' . 'Invoice_'.$myrow['dispatchid'] . '.'.$ext.'" height="100" width="100" />';
		
	} else {
		$StockImgLink = _('No Image');	
	}
}


	if ($StockImgLink!=_('No Image')){
	echo '' . _('Image') . '<br /><a target = "_blank" href = "'.$_SESSION['part_pics_dir'] . '/' .'Invoice_'.$myrow['dispatchid'].'.pdf'.'">download</a></td>';
	}
			
		// upload Invoice end

					//grb upload begin
					echo '
		
		<td><input type="file" id="GRB" name="GRB' .$myrow['dispatchid']. '" />
		 ';
		
		if($_SESSION['AccessLevel'] == 10 || $_SESSION['AccessLevel'] == 23 || $_SESSION['AccessLevel'] == 22)
		echo '<br /><input type="checkbox" name="ClearImageGRB' .$myrow['dispatchid']. '" id="ClearImageGRB' .$myrow['dispatchid']. '" value="1" > '._('Clear Image').'';
$path = $_FILES['GRB'.$i]['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION);

if (function_exists('imagecreatefromjpg')){
	$StockImgLink = '<img src="GRBSTStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
		'&amp;StockID='.urlencode('GRB_'.$myrow['dispatchid']).
		'&amp;text='.
		'&amp;width=100'.
		'&amp;height=100'.
		'" alt="" />';
} else {
	if( file_exists($_SESSION['part_pics_dir'] . '/' .'GRB_'.$myrow['dispatchid'].'.pdf') ) {
		$StockImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/' . 'GRB_'.$myrow['dispatchid'] . '.'.$ext.'" height="100" width="100" />';
		
	} else {
		$StockImgLink = _('No Image');	
	}
}


	if ($StockImgLink!=_('No Image')){
	echo '' . _('Image') . '<br /><a target = "_blank" href = "'.$_SESSION['part_pics_dir'] . '/' .'GRB_'.$myrow['dispatchid'].'.pdf'.'">download</a></td>';
	}
	
		// upload GRB end


echo '
<td><input type = "submit" value = "submit"</td>

</tr>

';
					
		
	echo '</table>';
		
}	
	
}	
	
	}

	
	

	
	echo '</form>
	';
	
 /* Show status button hit */
echo '</div>
      ';
include('includes/footer.inc');

?>