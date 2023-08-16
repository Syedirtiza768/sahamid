<!DOCTYPE html>
<html>
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

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SAHamid ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="asset/vendor/fonts/boxicons.css" />
    <!-- <link rel="stylesheet" href="asset/vendor/css/coreStyle.css" /> -->
    <link href="asset/vendor/css/select2s.css"" rel=" stylesheet" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />

</head>
<style>
    .ajax-loader {
        visibility: hidden;
        background-color: rgba(255, 255, 255, 0.7);
        position: absolute;
        z-index: +100 !important;
        width: 100%;
        height: 100%;
    }

    .ajax-loader img {
        position: relative;
        top: 30%;
        left: 36%;
    }

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
    <header class="fixed w-full shadow-md">
        <nav class="border-gray-200 py-3.5" style="box-shadow:inset 0px 3px 6px #00000029, 0px 3px 6px #00000029; background-color: #F5F5F5;">
            <div class="flex justify-center">
                <div><img class="h-8 w-8" src="dashV2-assets/img/header.png" alt="Leader"></img></div>
                <div>
                    <h5 class="ml-2 text-xl font-sans tracking-normal font-extrabold">
                        <?php echo $name; ?></h5>
                </div>
            </div>
        </nav>
    </header>

    <section class="bg-white pt-10">
        <div class="h-25 py-12 sm:mr-28">
            <div class="flex items-center justify-center">
                <div class="border lg:w-full lg:h-12 rounded-md sm:w-full sm:ml-16 sm:shadow-md" style="box-shadow: inset 0px 3px 6px #00000029, 0px 3px 6px #00000029; background-color: #F5F5F5;">
                    <div class="lg:flex items-center justify-between">
                        <div class="py-3 flex-none w-14 h-14"><img class="max-w-xl mx-auto w-6" src="dashV2-assets/img/member.png"></img></div>
                        <div class="grow h-10 w-auto"><select class="select select-bordered w-full max-w-xs js-example-basic-multiple select2" id="salesman" multiple="multiple">
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
                            </select></div>
                        <div class="py-3 flex-none w-14 h-14"><img class="max-w-xl mx-auto w-6" src="dashV2-assets/img/calender.png"></img></div>
                        <div class="grow h-8 w-auto">
                            <div class="mb-6">
                                <input type="text" style="background-color:#F5F5F5" class="form-control w-full" onfocus="(this.type='date')" id="from" placeholder="Start Date">
                            </div>
                        </div>
                        <div class="py-3 flex-none w-14 h-14"><img class="max-w-xl mx-auto w-6" src="dashV2-assets/img/calender.png"></img></div>
                        <div class="grow h-8 w-auto">
                            <div class="mb-6">
                                <input type="text" style="background-color:#F5F5F5" class="form-control w-full" onfocus="(this.type='date')" id="to" placeholder="End Date">
                            </div>
                        </div>
                        <div class="mr-6 ml-2 py-3 flex-none h-14 cursor-pointer"><img class="w-6" id="submit1" src="dashV2-assets/img/Icon feather-search@2x.png"></img></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="ajax-loader">
        <img src="asset/vendor/css/1495.gif" class="img-responsive" />
    </div>

    <section class="">
        <div class="grid lg:grid-cols-4 lg-gap-2 space-x-0 mx-16">
            <?php include('dashboard/badges/st-badge.php'); ?>
            <?php include('dashboard/badges/os-badge.php'); ?>
            <?php include('dashboard/badges/pendingdc-badge.php'); ?>
            <?php include('dashboard/badges/tc-badge.php'); ?>
        </div>
    </section>

    <section class="bg-white ">
        <div class="h-25 py-12 sm:mr-28">
        </div>
    </section>

    <section class="">

        <div class="grid lg:grid-cols-4 lg-gap-2 space-x-0 mx-16">
            <?php include('dashboard/badges/salescase-badge.php'); ?>
            <?php include('dashboard/badges/quotation-badge.php'); ?>
            <?php include('dashboard/badges/oc-badge.php'); ?>
            <?php include('dashboard/badges/dc-badge.php'); ?>
        </div>
    </section>

    <section class="bg-white pt-10 pb-16">
        <div class="flex items-center justify-center">
            <div class="lg:border lg:w-1/2 lg:h-36 rounded-lg sm:w-6/12 sm:shadow-md" style="box-shadow: inset 0px 3px 6px #00000029, 0px 25px 30px #00000033;">
                <div class="lg:flex items-center justify-between">
                <?php include('dashboard/badges/invoice-badge.php'); ?>
                </div>
            </div>
        </div>
    </section>

    <script src="asset/vendor/libs/jquery/jquery.js"></script>
    <script src="asset/vendor/js/bootstrap.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="dashboard/assets/js/d2javascript.js"></script>
</body>

</html>