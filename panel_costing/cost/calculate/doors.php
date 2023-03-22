<?php
require_once './config.php';
$pc_type = $_POST['pc_type'];
$conopy = $_POST['conopy'];
$door1_h = $_POST['door1_h'];
$door1_w = $_POST['door1_w'];
$door1_d = $_POST['door1_d'];
$door1_cp = $_POST['door1_cp'];
$door2_h = $_POST['door2_h'];
$door2_w = $_POST['door2_w'];
$door2_d = $_POST['door2_d'];
$door2_cp = $_POST['door2_cp'];
$door3_h = $_POST['door3_h'];
$door3_w = $_POST['door3_w'];
$door3_d = $_POST['door3_d'];
$door3_cp = $_POST['door3_cp'];
$door4_h = $_POST['door4_h'];
$door4_w = $_POST['door4_w'];
$door4_d = $_POST['door4_d'];
$door4_cp = $_POST['door4_cp'];
$door5_h = $_POST['door5_h'];
$door5_w = $_POST['door5_w'];
$door5_d = $_POST['door5_d'];
$door5_cp = $_POST['door5_cp'];
$door6_h = $_POST['door6_h'];
$door6_w = $_POST['door6_w'];
$door6_d = $_POST['door6_d'];
$door6_cp = $_POST['door6_cp'];
$door7_h = $_POST['door7_h'];
$door7_w = $_POST['door7_w'];
$door7_d = $_POST['door7_d'];
$door7_cp = $_POST['door7_cp'];
$door8_h = $_POST['door8_h'];
$door8_w = $_POST['door8_w'];
$door8_d = $_POST['door8_d'];
$door8_cp = $_POST['door8_cp'];
$id = $_POST['pc_id'];
$door1=0;
$door2=0;
$door3=0;
$door4=0;
$door5=0;
$door6=0;
$door7=0;
$door8=0;

$d1_front_total=0;$d2_front_total=0;$d3_front_total=0;$d4_front_total=0;
$d5_front_total=0;$d6_front_total=0;$d7_front_total=0;$d8_front_total=0;

if ($door1_h != "" && $door1_w != "" && $door1_d != "") {
    $d1_front_h = $door1_h / 1000;
    $d1_front_w = $door1_w / 1000;
    $thinkness = 0.002;
    if ($door1_cp == "yes") {
        $d1_front_total = $d1_front_h * $d1_front_w * $thinkness * 2;
    } elseif ($door1_cp == "no") {
        $d1_front_total = $d1_front_h * $d1_front_w * $thinkness;
    }

    $d1_rear_h = $door1_h / 1000;
    $d1_rear_w = $door1_w / 1000;
    $d1_rear_total = $d1_rear_h * $d1_rear_w * $thinkness;

    $d1_right_h = $door1_h / 1000;
    $d1_right_w = $door1_d / 1000;
    $d1_right_total = $d1_right_h * $d1_right_w * $thinkness;

    $d1_left_h = $door1_h / 1000;
    $d1_left_w = $door1_d / 1000;
    $d1_left_total = $d1_left_h * $d1_left_w * $thinkness;

    $d1_top_h = $door1_w / 1000;
    $d1_top_w = $door1_d / 1000;
    if ($conopy == "yes") {
        $d1_top_total = $d1_top_h * $d1_top_w * $thinkness * 3;
    } elseif ($conopy == "no") {
        $d1_top_total = $d1_top_h * $d1_top_w * $thinkness;
    }

    $d1_bottom_h = $door1_w / 1000;
    $d1_bottom_w = $door1_d / 1000;
    $d1_bottom_total = $d1_bottom_h * $d1_bottom_w * $thinkness;

    $d1_plate_h = $door1_h / 1000;
    $d1_plate_w = $door1_w / 1000;
    $d1_plate_total = $d1_plate_h * $d1_plate_w * $thinkness;

    $d1_angles_total =  $d1_front_total+$d1_rear_total+$d1_right_total+$d1_left_total+$d1_top_total+$d1_bottom_total+$d1_plate_total;
    $density= 7860;

    $d1_total= $d1_angles_total*$density;
    $d1_sheet = $d1_total/48;

    if($pc_type == "floor_mount" && $door1_h <=2000 && $door1_w <= 750)
    {
        $door1 = $d1_sheet*1.7;
    }
    elseif($pc_type == "wall_mount")
    {
        $door1 = $d1_sheet*1.25;
    }
    elseif($pc_type == "floor_mount" && $door1_h >=2100 && $door1_w < 750)
    {
        $door1 = $d1_sheet*1.61;
    }
    elseif($pc_type == "floor_mount" && ($door1_h >=2100 || $door1_h <=2000) && $door1_w > 750)
    {
        $door1 = $d1_sheet*1.5;
    }
}

if ($door2_h != ""  && $door2_w != "" && $door2_d != "") {
    $d2_front_h = $door2_h / 1000;
    $d2_front_w = $door2_w / 1000;
    $thinkness = 0.002;
    if ($door2_cp == "yes") {
        $d2_front_total = $d2_front_h * $d2_front_w * $thinkness * 2;
    } elseif ($door2_cp == "no") {
        $d2_front_total = $d2_front_h * $d2_front_w * $thinkness;
    }

    $d2_rear_h = $door2_h / 1000;
    $d2_rear_w = $door2_w / 1000;
    $d2_rear_total = $d2_rear_h * $d2_rear_w * $thinkness;

    $d2_right_h = $door2_h / 1000;
    $d2_right_w = $door2_d / 1000;
    $d2_right_total = $d2_right_h * $d2_right_w * $thinkness;

    $d2_left_h = $door2_h / 1000;
    $d2_left_w = $door2_d / 1000;
    $d2_left_total = $d2_left_h * $d2_left_w * $thinkness;

    $d2_top_h = $door2_w / 1000;
    $d2_top_w = $door2_d / 1000;
        $d2_top_total = $d2_top_h * $d2_top_w * $thinkness;

    $d2_bottom_h = $door2_w / 1000;
    $d2_bottom_w = $door2_d / 1000;
    $d2_bottom_total = $d2_bottom_h * $d2_bottom_w * $thinkness;

    $d2_plate_h = $door2_h / 1000;
    $d2_plate_w = $door2_w / 1000;
    $d2_plate_total = $d2_plate_h * $d2_plate_w * $thinkness;

    $d2_angles_total =  $d2_front_total+$d2_rear_total+$d2_right_total+$d2_left_total+$d2_top_total+$d2_bottom_total+$d2_plate_total;
    $density= 7860;

    $d2_total= $d2_angles_total*$density;
    $d2_sheet = $d2_total/48;

    if($pc_type == "floor_mount" && $door2_h <=2000 && $door2_w <= 750)
    {
        $door2 = $d2_sheet*1.3;
    }
    elseif($pc_type == "wall_mount")
    {
        $door2 = $d2_sheet*1.25;
    }
    elseif($pc_type == "floor_mount" && $door2_h >=2100 && $door2_w <= 750)
    {
        $door2 = $d2_sheet*1.1;
    }
    elseif($pc_type == "floor_mount" && ($door2_h >=2100 || $door2_h <=2000) && $door2_w > 750)
    {
        $door2 = $d2_sheet*1;
    }
}

if ($door3_h != "" && $door3_w != "" && $door3_d != "") {
    $d3_front_h = $door3_h / 1000;
    $d3_front_w = $door3_w / 1000;
    $thinkness = 0.002;
    if ($door3_cp == "yes") {
        $d3_front_total = $d3_front_h * $d3_front_w * $thinkness * 2;
    } elseif ($door3_cp == "no") {
        $d3_front_total = $d3_front_h * $d3_front_w * $thinkness;
    }

    $d3_rear_h = $door3_h / 1000;
    $d3_rear_w = $door3_w / 1000;
    $d3_rear_total = $d3_rear_h * $d3_rear_w * $thinkness;

    $d3_right_h = $door3_h / 1000;
    $d3_right_w = $door3_d / 1000;
    $d3_right_total = $d3_right_h * $d3_right_w * $thinkness;

    $d3_left_h = $door3_h / 1000;
    $d3_left_w = $door3_d / 1000;
    $d3_left_total = $d3_left_h * $d3_left_w * $thinkness;

    $d3_top_h = $door3_w / 1000;
    $d3_top_w = $door3_d / 1000;
        $d3_top_total = $d3_top_h * $d3_top_w * $thinkness;

    $d3_bottom_h = $door3_w / 1000;
    $d3_bottom_w = $door3_d / 1000;
    $d3_bottom_total = $d3_bottom_h * $d3_bottom_w * $thinkness;

    $d3_plate_h = $door3_h / 1000;
    $d3_plate_w = $door3_w / 1000;
    $d3_plate_total = $d3_plate_h * $d3_plate_w * $thinkness;

    $d3_angles_total =  $d3_front_total+$d3_rear_total+$d3_right_total+$d3_left_total+$d3_top_total+$d3_bottom_total+$d3_plate_total;
    $density= 7860;

    $d3_total= $d3_angles_total*$density;
    $d3_sheet = $d3_total/48;

    if($pc_type == "floor_mount" && $door3_h <=2000 && $door3_w <= 750)
    {
        $door3 = $d3_sheet*1.3;
    }
    elseif($pc_type == "wall_mount")
    {
        $door3 = $d3_sheet*1.25;
    }
    elseif($pc_type == "floor_mount" && $door3_h >=2000 && $door3_w <= 750)
    {
        $door3 = $d3_sheet*1.1;
    }
    elseif($pc_type == "floor_mount" && ($door3_h >=2100 || $door3_h <=2000) && $door3_w > 750)
    {
        $door3 = $d3_sheet*1;
    }
}

if ($door4_h != "" && $door4_w != "" && $door4_d != "") {
    $d4_front_h = $door4_h / 1000;
    $d4_front_w = $door4_w / 1000;
    $thinkness = 0.002;
    if ($door4_cp == "yes") {
        $d4_front_total = $d4_front_h * $d4_front_w * $thinkness * 2;
    } elseif ($door4_cp == "no") {
        $d4_front_total = $d4_front_h * $d4_front_w * $thinkness;
    }

    $d4_rear_h = $door4_h / 1000;
    $d4_rear_w = $door4_w / 1000;
    $d4_rear_total = $d4_rear_h * $d4_rear_w * $thinkness;

    $d4_right_h = $door4_h / 1000;
    $d4_right_w = $door4_d / 1000;
    $d4_right_total = $d4_right_h * $d4_right_w * $thinkness;

    $d4_left_h = $door4_h / 1000;
    $d4_left_w = $door4_d / 1000;
    $d4_left_total = $d4_left_h * $d4_left_w * $thinkness;

    $d4_top_h = $door4_w / 1000;
    $d4_top_w = $door4_d / 1000;
        $d4_top_total = $d4_top_h * $d4_top_w * $thinkness;

    $d4_bottom_h = $door4_w / 1000;
    $d4_bottom_w = $door4_d / 1000;
    $d4_bottom_total = $d4_bottom_h * $d4_bottom_w * $thinkness;

    $d4_plate_h = $door4_h / 1000;
    $d4_plate_w = $door4_w / 1000;
    $d4_plate_total = $d4_plate_h * $d4_plate_w * $thinkness;

    $d4_angles_total =  $d4_front_total+$d4_rear_total+$d4_right_total+$d4_left_total+$d4_top_total+$d4_bottom_total+$d4_plate_total;
    $density= 7860;

    $d4_total= $d4_angles_total*$density;
    $d4_sheet = $d4_total/48;

    if($pc_type == "floor_mount" && $door4_h <=2000 && $door4_w <= 750)
    {
        $door4 = $d4_sheet*1.3;
    }
    elseif($pc_type == "wall_mount")
    {
        $door4 = $d4_sheet*1.25;
    }
    elseif($pc_type == "floor_mount" && $door4_h >=2000 && $door4_w <= 750)
    {
        $door4 = $d4_sheet*1.1;
    }
    elseif($pc_type == "floor_mount" && ($door4_h >=2100 || $door4_h <=2000) && $door4_w > 750)
    {
        $door4 = $d4_sheet*1;
    }
}

if ($door5_h != "" && $door5_w != "" && $door5_d != "") {
    $d5_front_h = $door5_h / 1000;
    $d5_front_w = $door5_w / 1000;
    $thinkness = 0.002;
    if ($door5_cp == "yes") {
        $d5_front_total = $d5_front_h * $d5_front_w * $thinkness * 2;
    } elseif ($door5_cp == "no") {
        $d5_front_total = $d5_front_h * $d5_front_w * $thinkness;
    }

    $d5_rear_h = $door5_h / 1000;
    $d5_rear_w = $door5_w / 1000;
    $d5_rear_total = $d5_rear_h * $d5_rear_w * $thinkness;

    $d5_right_h = $door5_h / 1000;
    $d5_right_w = $door5_d / 1000;
    $d5_right_total = $d5_right_h * $d5_right_w * $thinkness;

    $d5_left_h = $door5_h / 1000;
    $d5_left_w = $door5_d / 1000;
    $d5_left_total = $d5_left_h * $d5_left_w * $thinkness;

    $d5_top_h = $door5_w / 1000;
    $d5_top_w = $door5_d / 1000;
        $d5_top_total = $d5_top_h * $d5_top_w * $thinkness;

    $d5_bottom_h = $door5_w / 1000;
    $d5_bottom_w = $door5_d / 1000;
    $d5_bottom_total = $d5_bottom_h * $d5_bottom_w * $thinkness;

    $d5_plate_h = $door5_h / 1000;
    $d5_plate_w = $door5_w / 1000;
    $d5_plate_total = $d5_plate_h * $d5_plate_w * $thinkness;

    $d5_angles_total =  $d5_front_total+$d5_rear_total+$d5_right_total+$d5_left_total+$d5_top_total+$d5_bottom_total+$d5_plate_total;
    $density= 7860;

    $d5_total= $d5_angles_total*$density;
    $d5_sheet = $d5_total/48;

    if($pc_type == "floor_mount" && $door5_h <=2000 && $door5_w <= 750)
    {
        $door5 = $d5_sheet*1.3;
    }
    elseif($pc_type == "wall_mount")
    {
        $door5 = $d5_sheet*1.25;
    }
    elseif($pc_type == "floor_mount" && $door5_h >=2000 && $door5_w <= 750)
    {
        $door5 = $d5_sheet*1.1;
    }
    elseif($pc_type == "floor_mount" && ($door5_h >=2100 || $door5_h <=2000) && $door5_w > 750)
    {
        $door5 = $d5_sheet*0.87;
    }
}

if ($door6_h != "" && $door6_w != "" && $door6_d != "") {
    $d6_front_h = $door6_h / 1000;
    $d6_front_w = $door6_w / 1000;
    $thinkness = 0.002;
    if ($door6_cp == "yes") {
        $d6_front_total = $d6_front_h * $d6_front_w * $thinkness * 2;
    } elseif ($door6_cp == "no") {
        $d6_front_total = $d6_front_h * $d6_front_w * $thinkness;
    }

    $d6_rear_h = $door6_h / 1000;
    $d6_rear_w = $door6_w / 1000;
    $d6_rear_total = $d6_rear_h * $d6_rear_w * $thinkness;

    $d6_right_h = $door6_h / 1000;
    $d6_right_w = $door6_d / 1000;
    $d6_right_total = $d6_right_h * $d6_right_w * $thinkness;

    $d6_left_h = $door6_h / 1000;
    $d6_left_w = $door6_d / 1000;
    $d6_left_total = $d6_left_h * $d6_left_w * $thinkness;

    $d6_top_h = $door6_w / 1000;
    $d6_top_w = $door6_d / 1000;
        $d6_top_total = $d6_top_h * $d6_top_w * $thinkness;

    $d6_bottom_h = $door6_w / 1000;
    $d6_bottom_w = $door6_d / 1000;
    $d6_bottom_total = $d6_bottom_h * $d6_bottom_w * $thinkness;

    $d6_plate_h = $door6_h / 1000;
    $d6_plate_w = $door6_w / 1000;
    $d6_plate_total = $d6_plate_h * $d6_plate_w * $thinkness;

    $d6_angles_total =  $d6_front_total+$d6_rear_total+$d6_right_total+$d6_left_total+$d6_top_total+$d6_bottom_total+$d6_plate_total;
    $density= 7860;

    $d6_total= $d6_angles_total*$density;
    $d6_sheet = $d6_total/48;

    if($pc_type == "floor_mount" && $door6_h <=2000 && $door6_w <= 750)
    {
        $door6 = $d6_sheet*1.3;
    }
    elseif($pc_type == "wall_mount")
    {
        $door6 = $d6_sheet*1.25;
    }
    elseif($pc_type == "floor_mount" && $door6_h >=2000 && $door6_w <= 750)
    {
        $door6 = $d6_sheet*1.1;
    }
    elseif($pc_type == "floor_mount" && ($door6_h >=2100 || $door6_h <=2000) && $door6_w > 750)
    {
        $door6 = $d6_sheet*0.98;
    }
}

if ($door7_h != "" && $door7_w != "" && $door7_d != "") {
    $d7_front_h = $door7_h / 1000;
    $d7_front_w = $door7_w / 1000;
    $thinkness = 0.002;
    if ($door7_cp == "yes") {
        $d7_front_total = $d7_front_h * $d7_front_w * $thinkness * 2;
    } elseif ($door7_cp == "no") {
        $d7_front_total = $d7_front_h * $d7_front_w * $thinkness;
    }

    $d7_rear_h = $door7_h / 1000;
    $d7_rear_w = $door7_w / 1000;
    $d7_rear_total = $d7_rear_h * $d7_rear_w * $thinkness;

    $d7_right_h = $door7_h / 1000;
    $d7_right_w = $door7_d / 1000;
    $d7_right_total = $d7_right_h * $d7_right_w * $thinkness;

    $d7_left_h = $door7_h / 1000;
    $d7_left_w = $door7_d / 1000;
    $d7_left_total = $d7_left_h * $d7_left_w * $thinkness;

    $d7_top_h = $door7_w / 1000;
    $d7_top_w = $door7_d / 1000;
        $d7_top_total = $d7_top_h * $d7_top_w * $thinkness;

    $d7_bottom_h = $door7_w / 1000;
    $d7_bottom_w = $door7_d / 1000;
    $d7_bottom_total = $d7_bottom_h * $d7_bottom_w * $thinkness;

    $d7_plate_h = $door7_h / 1000;
    $d7_plate_w = $door7_w / 1000;
    $d7_plate_total = $d7_plate_h * $d7_plate_w * $thinkness;

    $d7_angles_total =  $d7_front_total+$d7_rear_total+$d7_right_total+$d7_left_total+$d7_top_total+$d7_bottom_total+$d7_plate_total;
    $density= 7860;

    $d7_total= $d7_angles_total*$density;
    $d7_sheet = $d7_total/48;

    if($pc_type == "floor_mount")
    {
        $door7 = $d7_sheet*1.3;
    }
    elseif($pc_type == "wall_mount")
    {
        $door7 = $d7_sheet*1.25;
    }
}

if ($door8_h != "" && $door8_w != "" && $door8_d != "") {
    $d8_front_h = $door8_h / 1000;
    $d8_front_w = $door8_w / 1000;
    $thinkness = 0.002;
    if ($door8_cp == "yes") {
        $d8_front_total = $d8_front_h * $d8_front_w * $thinkness * 2;
    } elseif ($door8_cp == "no") {
        $d8_front_total = $d8_front_h * $d8_front_w * $thinkness;
    }

    $d8_rear_h = $door8_h / 1000;
    $d8_rear_w = $door8_w / 1000;
    $d8_rear_total = $d8_rear_h * $d8_rear_w * $thinkness;

    $d8_right_h = $door8_h / 1000;
    $d8_right_w = $door8_d / 1000;
    $d8_right_total = $d8_right_h * $d8_right_w * $thinkness;

    $d8_left_h = $door8_h / 1000;
    $d8_left_w = $door8_d / 1000;
    $d8_left_total = $d8_left_h * $d8_left_w * $thinkness;

    $d8_top_h = $door8_w / 1000;
    $d8_top_w = $door8_d / 1000;
        $d8_top_total = $d8_top_h * $d8_top_w * $thinkness;

    $d8_bottom_h = $door8_w / 1000;
    $d8_bottom_w = $door8_d / 1000;
    $d8_bottom_total = $d8_bottom_h * $d8_bottom_w * $thinkness;

    $d8_plate_h = $door8_h / 1000;
    $d8_plate_w = $door8_w / 1000;
    $d8_plate_total = $d8_plate_h * $d8_plate_w * $thinkness;

    $d8_angles_total =  $d8_front_total+$d8_rear_total+$d8_right_total+$d8_left_total+$d8_top_total+$d8_bottom_total+$d8_plate_total;
    $density= 7860;

    $d8_total= $d8_angles_total*$density;
    $d8_sheet = $d8_total/48;

    if($pc_type == "floor_mount")
    {
        $door8 = $d8_sheet*1.3;
    }
    elseif($pc_type == "wall_mount")
    {
        $door8 = $d8_sheet*1.25;
    }
}
$total_sheet = $door1+$door2+$door3+$door4+$door5+$door6+$door7+$door8;

session_start();
$_SESSION['total_sheet'] = $total_sheet;

// session_start();
// date_default_timezone_set("Asia/Karachi");
//  $date = date('d-m-y h:i:s');
// $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-1 ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
// if (mysqli_query($conn, $sql)) {
// }
// else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
//   }

$sql ="UPDATE panel_costing SET sheet_use = '".$total_sheet."'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo $total_sheet;
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
