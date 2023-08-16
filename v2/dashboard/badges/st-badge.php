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
// $name = strtolower($name);
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

$SQL = "SELECT * FROM salesman WHERE salesmanname IN($salesman)";
$res = mysqli_query($db, $SQL);
$totalTarget  = NULL;
while ($row = mysqli_fetch_assoc($res)) {
    $totalTarget = $row['target'] + $totalTarget;
}
$totalTarget = locale_number_format(round($totalTarget, 0));
?>

<div class="border rounded-lg w-full sm:w-10/12 sm:shadow-2xl">
    <div class="flex justify-start">
        <div class="mr-6 ml-6 py-5 flex-none"><img class="w-12" src="dashV2-assets/img/salesTarget.png"></img></div>
        <div class="flex flex-col">
            <div class="font-serif mt-4 font-semibold text-gray-400">
                <h3>Total Sales Target</h3>
            </div>
            <?php if ($totalTarget == NULL) { ?>
                <h3 class="text-red-500 text-lg font-bold" id="salestarget"><span id="usingCSSBlink"> 0 </span></h3>
            <?php } else { ?>
                <h3 class="text-red-500 text-lg font-bold" id="salestarget"><span id="usingCSSBlink"> <?php echo $totalTarget; ?> </span></h3>
            <?php } ?>
        </div>
    </div>
</div>
