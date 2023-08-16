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
    // $id = $_GET['Dashboard'];

    // $SQL = "SELECT * FROM user_dashboards WHERE dashboard_id='$id' AND user_id='" . $_SESSION['UserID'] . "'";
    // $res = mysqli_query($db, $SQL);

    // if (mysqli_num_rows($res) != 1) {
    //     $id = -1;
    // }
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

    <!-- Fonts -->
    <!-- <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" /> -->
    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="asset/vendor/fonts/boxicons.css" />
    <!-- <link rel="stylesheet" href="asset/vendor/css/classic.date.css">
    <link rel="stylesheet" href="asset/vendor/css/classic.css"> -->
    <!-- Core CSS -->
    <link rel="stylesheet" href="asset/vendor/css/coreStyle.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="asset/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="asset/vendor/css/styl.css" />
    <!-- <link rel="stylesheet" href="asset/vendor/css/main_css.css" /> -->

    <!-- Vendors CSS -->
    <link rel=" stylesheet" href="asset/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="asset/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS -->

    <link href="asset/vendor/css/select2s.css"" rel=" stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="asset/js/config.js"></script>
</head>
<style>
    #usingCSSBlink {
        color: red;
        animation: blinking-usingCSSBlink 1s linear infinite;
    }

    @keyframes blinking-usingCSSBlink {
        50% {
            opacity: 0;
        }
    }

    #usingCSSBlinkColor {
        color: white;
        animation: blinking-usingCSSBlinkColor 1s linear infinite;
    }

    @keyframes blinking-usingCSSBlinkColor {
        20% {
            color: red;
        }

        80% {
            color: white;
        }
    }
</style>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <div class="layout-page">
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar" style="position:absolute;
                 margin:auto; background-color:red; width: 100% !important">
                    <div class="navbar-nav align-items-left">
                        <div class="nav-item d-flex align-items-left name">
                            <div class="twelve">
                                <h1><b><?php echo $name; ?></b></h1>
                            </div>
                        </div>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <!-- Search -->
                        <div class="navbar-nav align-items-center">

                        </div>
                        <!-- /Search -->

                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- Place this tag where you want the button to render. -->
                            <li class="nav-item lh-1 me-3">
                                <button class="btn btn-success" onclick="location.href='api/clearCache.php'"><i class="fa fa-refresh fa-spin"></i> | Reload</button>
                            </li>

                            <!-- User -->
                        </ul>
                    </div>
                </nav> <br><br>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-lg-12 mb-4 order-0">
                                <div style="background-color:#f5f5f9; height:25%">
                                    <div class="d-flex  row">
                                        <div class="col-sm-5 text-center text-sm-left">
                                            <div class="card-body pb-0 px-0 px-md-4">
                                                <!-- <img src="asset/img/illustrations/man-with-laptop-light.png" height="140" alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png" /> -->
                                            </div>
                                        </div>
                                        <div class="col-sm-7">
                                            <div class="card-body">
                                                <form class="flex-form">
                                                    <i class="fa fa-user" style="color:#ef3f5a;font-size:30px"></i>
                                                    <select class="js-example-basic-multiple select2" id="salesman" name="cars" multiple="multiple">
                                                        <?php
                                                        $SQL = "SELECT * FROM salesteam WHERE lead='$name'";
                                                        $res = mysqli_query($db, $SQL); ?>
                                                        <optgroup label="Team (Leader)" class="select2-result-selectable">
                                                            <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
                                                        </optgroup>
                                                        <?php while ($row = mysqli_fetch_assoc($res)) { ?>
                                                            <optgroup label="Team (<?php echo $row["name"] ?>)" class="select2-result-selectable">
                                                                <?php $members = explode(',', $row["members"]);
                                                                foreach ($members as $member) { ?>
                                                                    <option value="<?php echo $member; ?>"><?php echo $member; ?></option>
                                                                <?php } ?>
                                                            </optgroup>
                                                        <?php }
                                                        ?>
                                                    </select>
                                                    <!-- <i class="fa fa-calendar" style="color:#ef3f5a;font-size:30px"></i>
                                                    <input type="text" class="form-control" onfocus="(this.type='date')" id="from" placeholder="Start Date">
                                                    <i class="fa fa-calendar" style="color:#ef3f5a;font-size:30px"></i>
                                                    <input type="text" class="form-control" onfocus="(this.type='date')" id="to" placeholder="End Date"> -->
                                                    <input type="submit" id="submit" value="Search">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="ajax-loader">
                                <img src="asset/vendor/css/1495.gif" class="img-responsive" />
                            </div>

                            <!-- updated Badges file -->
                            

                            <div class="col-lg-12 mb-4 order-0">
                                <div style="background-color:#f5f5f9; height:25%">
                                    <div class="d-flex  row">
                                        <div class="col-sm-5 text-center text-sm-left">
                                            <div class="card-body pb-0 px-0 px-md-4">
                                                <!-- <img src="asset/img/illustrations/man-with-laptop-light.png" height="140" alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png" /> -->
                                            </div>
                                        </div>
                                        <div class="col-sm-7">
                                            <div class="card-body">
                                                <form class="flex-form">
                                                    <i class="fa fa-calendar" style="color:#ef3f5a;font-size:30px"></i>
                                                    <input type="text" class="form-control" onfocus="(this.type='date')" id="from" placeholder="Start Date">
                                                    <i class="fa fa-calendar" style="color:#ef3f5a;font-size:30px"></i>
                                                    <input type="text" class="form-control" onfocus="(this.type='date')" id="to" placeholder="End Date">
                                                    <input type="submit" id="submit1" value="Search">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <?php include('dashboard/badges/salescase-badge.php'); ?>
                                <?php include('dashboard/badges/quotation-badge.php'); ?>
                                <?php include('dashboard/badges/oc-badge.php'); ?>
                                <?php include('dashboard/badges/dc-badge.php'); ?>
                                <?php include('dashboard/badges/invoice-badge.php'); ?>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->



                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Core JS -->
        <!-- build:js assets/vendor/js/core.js -->
        <script src="asset/vendor/libs/jquery/jquery.js"></script>
        <script src="asset/vendor/libs/popper/popper.js"></script>
        <script src="asset/vendor/js/bootstrap.js"></script>
        <script src="asset/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

        <script src="asset/vendor/js/picker.js"></script>
        <script src="asset/vendor/js/picker.date.js"></script>
        <script src="asset/vendor/js/menu.js"></script>
        <script src="asset/vendor/js/main.js"></script>
        <!-- endbuild -->

        <!-- Vendors JS -->
        <script src="asset/vendor/libs/apex-charts/apexcharts.js"></script>

        <!-- Main JS -->
        <!-- <script src="asset/js/main.js"></script> -->

        <!-- Page JS -->
        <script src="asset/js/dashboards-analytics.js"></script>
        <script src="dashboard/assets/js/DV2Files.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
        <!-- Place this tag in your head or just before your close body tag. -->
        <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
<?php
include_once("includes/foot.php");
?>