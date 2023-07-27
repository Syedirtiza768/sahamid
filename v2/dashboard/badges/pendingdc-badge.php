<?php

if (!isset($db)) {
    session_start();
    $db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
    return;
}

$allowed = [8, 10, 22];

$SQL = "SELECT * FROM salesteam WHERE lead='" . $name . "'";
$res = mysqli_query($db, $SQL);
$members = [];
if ($res === FALSE) {
    $salesman = "'  $name  '";
} else {
	while ($row = mysqli_fetch_assoc($res)) {
		$members[] = $row['members'];
	}
	$salesman = implode(',', $members);
	$choices = explode(",", $salesman);
	$salesman = "'" . implode("','", $choices) . "";
    $salesman = $salesman . "','" . $name . "'";
}


$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "'";
$res = mysqli_query($db, $SQL);

$canAccess = [];

while ($row = mysqli_fetch_assoc($res))
    $canAccess[] = $row['can_access'];

$SQL = "SELECT dcs.orderno as dcno,dcs.orddate as date,dcs.salescaseref,debtorsmaster.name as client,
			salescase.salesman,debtorsmaster.dba,SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	) as totalamount,dcs.gst, CASE  WHEN  dcs.gst LIKE '%inclusive%' THEN SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	)*0.83 ELSE SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	)   END as exclusivegsttotalamount from dcdetails INNER JOIN dcoptions on (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
		INNER JOIN dcs on dcs.orderno = dcdetails.orderno
		INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
		INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
		WHERE salescase.salesman IN( $salesman)
        AND dcdetails.lineoptionno = 0  
		AND dcoptions.optionno = 0 
		AND dcs.orddate <= '" . date("Y-m-31 23:59:59") . "'
		AND dcs.orddate >= '" . date("Y-m-01 00:00:00") . "'
		AND dcs.courierslipdate = '0000-00-00 00:00:00' AND dcs.invoicedate='0000-00-00 00:00:00' 
		AND dcs.grbdate='0000-00-00 00:00:00'
		AND dcs.invoicegroupid is null
		GROUP BY dcs.orderno
		";

$totalPending = NULL;
$resp = mysqli_query($db, $SQL);
while ($row = mysqli_fetch_assoc($resp)) {
    $totalPending = round($row['totalamount']) + $totalPending;
}
$totalPending = locale_number_format(round($totalPending,0));
?>


<div class="border rounded-lg sm:w-11/12 shadow-2xl">
    <div class="flex justify-start">
        <div class="mr-6 ml-6 py-5 flex-none"><img class="w-12" src="dashV2-assets/img/os.png"></img></div>
        <div class="flex flex-col">
            <div class="font-serif mt-4 font-semibold text-gray-400">
                <h3>Total Pending DC's</h3>
            </div>
            <?php if ($totalPending == NULL) { ?>
                <div class="text-red-500 text-lg font-bold" id="pdcCount"><span id="usingCSSBlink"> 0 </span></div>
            <?php } else { ?>
                <div class="text-red-500 text-lg font-bold" id="pdcCount"><span id="usingCSSBlink"> <?php echo $totalPending ?> </span></div>
            <?php } ?>
        </div>
    </div>
</div>