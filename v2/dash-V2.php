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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="asset/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="asset/vendor/css/classic.date.css">
    <link rel="stylesheet" href="asset/vendor/css/classic.css">
    <!-- Core CSS -->
    <link rel="stylesheet" href="asset/vendor/css/coreStyle.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="asset/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="asset/vendor/css/styl.css" />
    <link rel="stylesheet" href="asset/vendor/css/main_css.css" />

    <!-- Vendors CSS -->
    <link rel=" stylesheet" href="asset/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="asset/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS -->

    <link href="asset/vendor/css/select2s.css"" rel=" stylesheet" />

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
    #usingCSSBlinkColor{
        color: white;
        animation: blinking-usingCSSBlinkColor 1s linear infinite;
    }
    @keyframes blinking-usingCSSBlinkColor {
    20% {
    color: red;
    }
    80%{
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
                            <div class="row">
                                <?php include('dashboard/badges/st-badge.php'); ?>
                                <?php include('dashboard/badges/os-badge.php'); ?>
                                <?php include('dashboard/badges/pendingdc-badge.php'); ?>
                                <?php include('dashboard/badges/tc-badge.php'); ?>
                            </div>

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
                            <!-- Total Revenue -->
                            <!-- <div class="col-12 col-lg-8 order-2 order-md-3 order-lg-2 mb-4">
                                <div class="card">
                                    <div class="row row-bordered g-0 salescase">
                                        <div class="col-md-8">
                                            <h5 class="card-header m-0 me-2 pb-3">Total Revenue</h5>
                                            <div id="totalRevenueChart" class="px-2"></div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="growthReportId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            2022
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="growthReportId">
                                                            <a class="dropdown-item" href="javascript:void(0);">2021</a>
                                                            <a class="dropdown-item" href="javascript:void(0);">2020</a>
                                                            <a class="dropdown-item" href="javascript:void(0);">2019</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="growthChart"></div>
                                            <div class="text-center fw-semibold pt-3 mb-2">62% Company Growth</div>

                                            <div class="d-flex px-xxl-4 px-lg-2 p-4 gap-xxl-3 gap-lg-1 gap-3 justify-content-between">
                                                <div class="d-flex">
                                                    <div class="me-2">
                                                        <span class="badge bg-label-primary p-2"><i class="bx bx-dollar text-primary"></i></span>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <small>2022</small>
                                                        <h6 class="mb-0">$32.5k</h6>
                                                    </div>
                                                </div>
                                                <div class="d-flex">
                                                    <div class="me-2">
                                                        <span class="badge bg-label-info p-2"><i class="bx bx-wallet text-info"></i></span>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <small>2021</small>
                                                        <h6 class="mb-0">$41.2k</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                        <!-- <div class="row">
                            <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-4 salescase">
                                <div class="card h-100" style="background-color:#f5f5f9">
                                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                                        <div class="card-title mb-0">
                                            <h5 class="m-0 me-2">Order Statistics</h5>
                                            <small class="text-muted">42.82k Total Sales</small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn p-0" type="button" id="orederStatistics" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="orederStatistics">
                                                <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                                                <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                                <a class="dropdown-item" href="javascript:void(0);">Share</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                <h2 class="mb-2">8,258</h2>
                                                <span>Total Orders</span>
                                            </div>
                                            <div id="orderStatisticsChart"></div>
                                        </div>
                                        <ul class="p-0 m-0">
                                            <li class="d-flex mb-4 pb-1">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-mobile-alt"></i></span>
                                                </div>
                                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <h6 class="mb-0">Electronic</h6>
                                                        <small class="text-muted">Mobile, Earbuds, TV</small>
                                                    </div>
                                                    <div class="user-progress">
                                                        <small class="fw-semibold">82.5k</small>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex mb-4 pb-1">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <span class="avatar-initial rounded bg-label-success"><i class="bx bx-closet"></i></span>
                                                </div>
                                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <h6 class="mb-0">Fashion</h6>
                                                        <small class="text-muted">T-shirt, Jeans, Shoes</small>
                                                    </div>
                                                    <div class="user-progress">
                                                        <small class="fw-semibold">23.8k</small>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex mb-4 pb-1">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <span class="avatar-initial rounded bg-label-info"><i class="bx bx-home-alt"></i></span>
                                                </div>
                                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <h6 class="mb-0">Decor</h6>
                                                        <small class="text-muted">Fine Art, Dining</small>
                                                    </div>
                                                    <div class="user-progress">
                                                        <small class="fw-semibold">849k</small>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <span class="avatar-initial rounded bg-label-secondary"><i class="bx bx-football"></i></span>
                                                </div>
                                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <h6 class="mb-0">Sports</h6>
                                                        <small class="text-muted">Football, Cricket Kit</small>
                                                    </div>
                                                    <div class="user-progress">
                                                        <small class="fw-semibold">99</small>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 order-1 mb-4 salescase">
                                <div class="card h-100" style="background-color:#f5f5f9">
                                    <div class="card-header">
                                        <ul class="nav nav-pills" role="tablist">
                                            <li class="nav-item">
                                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tabs-line-card-income" aria-controls="navs-tabs-line-card-income" aria-selected="true">
                                                    Income
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab">Expenses</button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab">Profit</button>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-body px-0">
                                        <div class="tab-content p-0">
                                            <div class="tab-pane fade show active" id="navs-tabs-line-card-income" role="tabpanel">
                                                <div class="d-flex p-4 pt-3">
                                                    <div class="avatar flex-shrink-0 me-3">
                                                        <img src="asset/img/icons/unicons/wallet.png" alt="User" />
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Total Balance</small>
                                                        <div class="d-flex align-items-center">
                                                            <h6 class="mb-0 me-1">$459.10</h6>
                                                            <small class="text-success fw-semibold">
                                                                <i class="bx bx-chevron-up"></i>
                                                                42.9%
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="incomeChart"></div>
                                                <div class="d-flex justify-content-center pt-4 gap-2">
                                                    <div class="flex-shrink-0">
                                                        <div id="expensesOfWeek"></div>
                                                    </div>
                                                    <div>
                                                        <p class="mb-n1 mt-1">Expenses This Week</p>
                                                        <small class="text-muted">$39 less than last week</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 order-2 mb-4 salescase">
                                <div class="card h-100" style="background-color:#f5f5f9">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h5 class="card-title m-0 me-2">Transactions</h5>
                                        <div class="dropdown">
                                            <button class="btn p-0" type="button" id="transactionID" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                                                <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
                                                <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                                                <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <ul class="p-0 m-0">
                                            <li class="d-flex mb-4 pb-1">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <img src="asset/img/icons/unicons/paypal.png" alt="User" class="rounded" />
                                                </div>
                                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <small class="text-muted d-block mb-1">Paypal</small>
                                                        <h6 class="mb-0">Send money</h6>
                                                    </div>
                                                    <div class="user-progress d-flex align-items-center gap-1">
                                                        <h6 class="mb-0">+82.6</h6>
                                                        <span class="text-muted">USD</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex mb-4 pb-1">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <img src="asset/img/icons/unicons/wallet.png" alt="User" class="rounded" />
                                                </div>
                                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <small class="text-muted d-block mb-1">Wallet</small>
                                                        <h6 class="mb-0">Mac'D</h6>
                                                    </div>
                                                    <div class="user-progress d-flex align-items-center gap-1">
                                                        <h6 class="mb-0">+270.69</h6>
                                                        <span class="text-muted">USD</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex mb-4 pb-1">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <img src="asset/img/icons/unicons/chart.png" alt="User" class="rounded" />
                                                </div>
                                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <small class="text-muted d-block mb-1">Transfer</small>
                                                        <h6 class="mb-0">Refund</h6>
                                                    </div>
                                                    <div class="user-progress d-flex align-items-center gap-1">
                                                        <h6 class="mb-0">+637.91</h6>
                                                        <span class="text-muted">USD</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex mb-4 pb-1">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <img src="asset/img/icons/unicons/cc-success.png" alt="User" class="rounded" />
                                                </div>
                                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <small class="text-muted d-block mb-1">Credit Card</small>
                                                        <h6 class="mb-0">Ordered Food</h6>
                                                    </div>
                                                    <div class="user-progress d-flex align-items-center gap-1">
                                                        <h6 class="mb-0">-838.71</h6>
                                                        <span class="text-muted">USD</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex mb-4 pb-1">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <img src="asset/img/icons/unicons/wallet.png" alt="User" class="rounded" />
                                                </div>
                                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <small class="text-muted d-block mb-1">Wallet</small>
                                                        <h6 class="mb-0">Starbucks</h6>
                                                    </div>
                                                    <div class="user-progress d-flex align-items-center gap-1">
                                                        <h6 class="mb-0">+203.33</h6>
                                                        <span class="text-muted">USD</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <img src="asset/img/icons/unicons/cc-warning.png" alt="User" class="rounded" />
                                                </div>
                                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <small class="text-muted d-block mb-1">Mastercard</small>
                                                        <h6 class="mb-0">Ordered Food</h6>
                                                    </div>
                                                    <div class="user-progress d-flex align-items-center gap-1">
                                                        <h6 class="mb-0">-92.45</h6>
                                                        <span class="text-muted">USD</span>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div> -->
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
        <script src="dashboard/assets/js/DV2.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
        <!-- Place this tag in your head or just before your close body tag. -->
        <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
<?php
include_once("includes/foot.php");
?>