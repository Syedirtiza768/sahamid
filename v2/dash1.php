<?php

$AllowAnyone = true;

$active = "dashboard";

include_once("config.php");
include_once("dashboard/availableBadges.php");
include_once("dashboard/availableWidgets.php");


$id = 0;

if (isset($_GET['Dashboard'])) {
    $id = $_GET['Dashboard'];

    $SQL = "SELECT * FROM user_dashboards WHERE dashboard_id='$id' AND user_id='" . $_SESSION['UserID'] . "'";
    $res = mysqli_query($db, $SQL);

    if (mysqli_num_rows($res) != 1) {
        $id = -1;
    }
}

if (isset($_POST['salesman'])) {
    echo $_POST['$salesman'];
}

$SQL = "SELECT * FROM dashboards WHERE id='$id'";
$res = mysqli_query($db, $SQL);

$badges = [];
$widgets = [];
if (mysqli_num_rows($res) != 1) {

    $name = "";
    $badges = [];
    $widgets = [];
} else {

    $res = mysqli_fetch_assoc($res);
    $name = $res['name'];
    $badges = explode(",", $res['badges']);
    $widgets = explode(",", $res['widgets']);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./dashV2-assets/css/style.css" />
    <title>SAHamid ERP</title>
</head>

<body>
    <div class="layout-wrapper">
        <div class="container">
            <div class="layout-navbar">
                <img src="./dashV2-assets/img/nav-icon.png" class="nav-icon">
            </div>
        </div>
    </div>

    <script src="dashboard/assets/js/DV2.js"></script>
</body>
<?php
include_once("includes/foot.php");
?>

</html>