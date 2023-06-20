<?php

$AllowAnyone = true;

$active = "dashboard";

include_once("config.php");
include_once("dashboard/availableBadges.php");
include_once("dashboard/availableWidgets.php");

// include_once("includes/header.php");
// include_once("includes/sidebar.php");

$id = 0;

if (isset($_GET['Dashboard'])) {
    $id = $_GET['Dashboard'];

    $SQL = "SELECT * FROM user_dashboards WHERE dashboard_id='$id' AND user_id='" . $_SESSION['UserID'] . "'";
    $res = mysqli_query($db, $SQL);

    if (mysqli_num_rows($res) != 1) {
        $id = -1;
    }
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

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
    <!-- <link rel="icon" type="image/png" href="assets/img/favicon.png"> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>SAHamid ERP</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
    <!-- CSS Files -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/light-bootstrap-dashboard.css" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="assets/css/demo.css" rel="stylesheet" />





    <script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="<?php echo $NewRootPath; ?>v2/assets/plugins/highcharts.js"></script>
    <script src="<?php echo $NewRootPath; ?>v2/assets/plugins/funnel.js"></script>

</head>
<style>
    .badges,
    .grid {
        position: relative
    }

    .item {
        display: block;
        position: absolute;
        z-index: 1
    }

    .item.muuri-item-dragging {
        z-index: 3
    }

    .item.muuri-item-releasing {
        z-index: 2
    }

    .item.muuri-item-hidden {
        z-index: 0
    }

    .item-content {
        position: relative;
        width: 100%;
        height: 100%
    }

    .grid .item {
        margin-top: 10px
    }

    .dashboard-configuration {
        background: #222d32;
        width: 400px;
        height: calc(100vh - 14px);
        position: fixed;
        right: -400px;
        top: 51px;
        z-index: 100;
        transition: right .4s ease-out
    }

    .fa-gear {
        position: absolute;
        left: -39px;
        background: #fff;
        font-size: 1.4em;
        padding: 10px;
        box-shadow: 0 2px 8px 2px;
        border-radius: 8px 0 0 8px;
        border: 1px solid #424242;
        cursor: pointer
    }

    .available-badges,
    .available-widgets {
        width: 100%;
        overflow: hidden
    }

    .badges-list,
    .widget-list {
        overflow: hidden
    }

    .widget {
        display: inline-block;
        min-width: 10px;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        background-color: #777
    }

    .badge,
    .widget {
        border: 1px solid #fff;
        border-radius: 7px;
        padding: 7px;
        margin: 5px;
        font-size: .7em;
        cursor: pointer
    }

    body {
        zoom: .95 !important
    }

    .removeBadge,
    .removeWidget {
        display: none !important
    }
</style>
<div class="content-wrapper">

    <div class="wrapper">
        <!-- Heading and Buttons -->
        <div class="container-fluid">
            <div class="header">
                <div class="twelve">
                    <h1><?php echo $name; ?></h1>
                </div>
                <div class="buttons" style="float:right">
                    <?php if (userHasPermission($db, 'create_new_dashboard') && $id != -1) { ?>
                        <button class="btn btn-success"><i class="fa fa-edit"></i> | Edit</button>
                    <?php } ?>
                    <button class="btn btn-danger" onclick="location.href='api/clearCache.php'"><i class="fa fa-refresh fa-spin"></i> | Reload</button>
                </div>
            </div>
        </div>
        <!-- Heading and Buttons -->
        <div class="content">
            <div class="container-fluid">
                <div class="ten">
                    <h4>Badges</h4>
                </div>
                <div class="row">
                    <?php for ($i = 0; $i < count($badges); $i++) {
                        if (
                            $badges[$i] == "" ||
                            !file_exists('dashboard/badges/' . $badges[$i] . '.php') ||
                            !canAccessWidget($db, $badges[$i])
                        )
                            continue;
                        include('dashboard/badges/' . $badges[$i] . '.php');
                    } ?>
                </div>

                <div class="ten">
                    <h4>Widgets</h4>
                </div>
                <div class="row">
                    <?php for ($i = 0; $i < count($widgets); $i++) { ?>
                    <?php if (
                            $widgets[$i] == "" ||
                            !file_exists('dashboard/new_widgets/' . $widgets[$i] . '.php') ||
                            !canAccessWidget($db, $widgets[$i])
                        )
                            continue;
                        include('dashboard/new_widgets/' . $widgets[$i] . '.php');
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="dashboard/assets/js/muuri.js"></script>
<script>
    var FormID = '<?php echo $_SESSION['FormID']; ?>';
    $(document).ready(function() {
        let widgetsGrid = new Muuri('.grid', {
            dragEnabled: false
        });
        let badgesGrid = new Muuri('.badges', {
            dragEnabled: false
        });
    });
</script>
<!--   Core JS Files   -->
<script src="assets/js/core/jquery.3.2.1.min.js" type="text/javascript"></script>
<script src="assets/js/core/popper.min.js" type="text/javascript"></script>
<script src="assets/js/core/bootstrap.min.js" type="text/javascript"></script>
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
<script src="assets/js/light-bootstrap-dashboard.js?v=2.0.1" type="text/javascript"></script>
<!-- Light Dashboard DEMO methods, don't include it in your project! -->
<script src="assets/js/demo.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Javascript method's body can be found in assets/js/demos.js
        demo.initDashboardPageCharts();

        // demo.showNotification();

        demo.initVectorMap();

    });
</script>
<?php
include_once("includes/foot.php");
?>