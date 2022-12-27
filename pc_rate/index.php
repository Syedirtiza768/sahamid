<?php
$PathPrefix='../';

include('../includes/session.inc');
include('../includes/SQL_CommonFunctions.inc');
require_once 'assets/config.php';
if(!userHasPermission($db, 'mpi_return')) {


    header("Location: /sahamid/v2/reportLinks.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="links/jquery.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="styless.css">

</head>
<body>
  <div class="container">
  <center>  <h1><b>Cost Sheet Price</b></h1> </center>
  <hr>
  <?php
$SQL = 'SELECT * FROM pc_rate  WHERE id="1"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <label> <h3><b>SHEET PRICE/KG:</b></h3> </label><br>
  <label>MS SHEET:</label>
<input type="number" style="width:10%" value="<?php echo $panelCost['ms_sheet']; ?>" name="ms_sheet" placeholder= "MS SHEET" id="ms_sheet" required >
<button class="eye" data-toggle="modal" data-target="#mssheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;SS SHEET:</label>
<input type="number" style="width:10%" value="<?php echo $panelCost['ss_sheet']; ?>" name="ss_sheet" placeholder= "SS SHEET" id="ss_sheet" required />
<button class="eye" data-toggle="modal" data-target="#sssheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;GI SHEET:</label>
<input type="number" style="width:10%" value="<?php echo $panelCost['gi_sheet']; ?>" name="gi_sheet" placeholder= "GI SHEET" id="gi_sheet" required />
<button class="eye" data-toggle="modal" data-target="#gisheet"><i class="fa fa-eye" aria-hidden="true"></i></button><br>

<label> <h3><b>PAINT COST:</b></h3> </label> <br>
<label>Depth(100-200):</label><br>
<label>M/F(7032)</label>
<input type="number" name="h_7032" value="<?php echo $panelCost['h_7032']; ?>" placeholder="M/F(7032)  " id="h_7032" required >
<button class="eye" data-toggle="modal" data-target="#h7032sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;M/F(7035)</label>
<input type="number" name="h_7035" value="<?php echo $panelCost['h_7035']; ?>" placeholder="  M/F(7035)" id="h_7035" required />
<button class="eye" data-toggle="modal" data-target="#h7035sheet"><i class="fa fa-eye" aria-hidden="true"></i></button><br>

<label>Depth(250-300):</label> <br>
<label>M/F(7032)</label>
<input type="number" name="tf_7032" value="<?php echo $panelCost['tf_7032']; ?>" placeholder="M/F(7032)" id="tf_7032" required />
<button class="eye" data-toggle="modal" data-target="#tf7032sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;M/F(7035)</label>
<input type="number" name="tf_7035" value="<?php echo $panelCost['tf_7035']; ?>" placeholder="M/F(7035)" id="tf_7035" required />
<button class="eye" data-toggle="modal" data-target="#tf7035sheet"><i class="fa fa-eye" aria-hidden="true"></i></button><br>

<label>Depth(350-450):</label><br>
<label>M/F(7032)</label>
<input type="number" name="thf_7032" value="<?php echo $panelCost['thf_7032']; ?>" placeholder="M/F(7032)" id="thf_7032" required />
<button class="eye" data-toggle="modal" data-target="#thf7032sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;M/F(7035)</label>
<input type="number" name="thf_7035" value="<?php echo $panelCost['thf_7035']; ?>" placeholder="M/F(7035)" id="thf_7035" required />
<button class="eye" data-toggle="modal" data-target="#thfshet7035"><i class="fa fa-eye" aria-hidden="true"></i></button><br>

<label>Depth(500-600):</label><br>
<label>M/F(7032)</label>
<input type="number" name="f_7032" value="<?php echo $panelCost['f_7032']; ?>" placeholder="M/F(7032)" id="f_7032" required />
<button class="eye" data-toggle="modal" data-target="#f7032sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;M/F(7035)</label>
<input type="number" name="f_7035" value="<?php echo $panelCost['f_7035']; ?>" placeholder="M/F(7035)" id="f_7035" required />
<button class="eye" data-toggle="modal" data-target="#f7035sheet"><i class="fa fa-eye" aria-hidden="true"></i></button><br>

<label>Depth(700-800):</label><br>
<label>M/F(7032)</label>
<input type="number" name="s_7032" value="<?php echo $panelCost['s_7032']; ?>" placeholder="M/F(7032)" id="s_7032" required />
<button class="eye" data-toggle="modal" data-target="#s7032sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;M/F(7035)</label>
<input type="number" name="s_7035" value="<?php echo $panelCost['s_7035']; ?>" placeholder="M/F(7035)" id="s_7035" required />
<button class="eye" data-toggle="modal" data-target="#s7035sheet"><i class="fa fa-eye" aria-hidden="true"></i></button><br>

<label>Depth(900-1200):</label> <br>
<label>M/F(7032)</label>
<input type="number" name="n_7032" value="<?php echo $panelCost['n_7032']; ?>" placeholder="M/F(7032)" id="n_7032" required />
<button class="eye" data-toggle="modal" data-target="#n7032sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;M/F(7035)</label>
<input type="number" name="n_7035" value="<?php echo $panelCost['n_7035']; ?>" placeholder="M/F(7035)" id="n_7035" required />
<button class="eye" data-toggle="modal" data-target="#n7035sheet"><i class="fa fa-eye" aria-hidden="true"></i></button><br>



<label> <h3><b>HINGES:</b></h3> </label> <br>
<label>HL-030:</label>
<input type="number" style="width:15%" value="<?php echo $panelCost['hl_030']; ?>" name="hl_030" placeholder="HL_030" id="hl_030" required />
<button class="eye" data-toggle="modal" data-target="#hl030sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;HL_027:</label>
<input type="number" style="width:15%" value="<?php echo $panelCost['hl_027']; ?>" name="hl_027" placeholder="HL_027" id="hl_027" required />
<button class="eye" data-toggle="modal" data-target="#hl027sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;HL_056:</label>
<input type="number" style="width:15%" value="<?php echo $panelCost['hl_056']; ?>" name="hl_056" placeholder="HL_056" id="hl_056" required />
<button class="eye" data-toggle="modal" data-target="#hl056sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;&ensp;HL_051:</label>
<input type="number" style="width:15%" value="<?php echo $panelCost['hl_051']; ?>" name="hl_051" placeholder="HL_051" id="hl_051" required />
<button class="eye" data-toggle="modal" data-target="#hl051sheet"><i class="fa fa-eye" aria-hidden="true"></i></button><br>



<label> <h3><b>LOCK:</b></h3> </label> <br>
<label>MS-480:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['ms_480']; ?>" name="ms_480" placeholder="MS-480" id="ms_480" required />
<button class="eye" data-toggle="modal" data-target="#ms480sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;MS-408:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['ms_408']; ?>" name="ms_408" placeholder="MS_408" id="ms_408" required />
<button class="eye" data-toggle="modal" data-target="#ms408sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;BNL-22:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['bnl_22']; ?>" name="bnl_22" placeholder="BNL-22" id="bnl_22" required />
<button class="eye" data-toggle="modal" data-target="#bnl22sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;PL_130:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['pl_130']; ?>" name="pl_130" placeholder="PL-130" id="pl_130" required />
<button class="eye" data-toggle="modal" data-target="#pl130sheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;PL_150:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['pl_150']; ?>" name="pl_150" placeholder="PL-150" id="pl_150" required />
<button class="eye" data-toggle="modal" data-target="#pl150sheet"><i class="fa fa-eye" aria-hidden="true"></i></button><br>
<br>
<label> <h3><b>ACRYLIC SHEET:</b></h3> </label>
<input type="number" name="acrylic_sheet" value="<?php echo $panelCost['acrylic_sheet']; ?>" placeholder="ACRYLIC SHEET" id="acrylic_sheet" required />
<button class="eye" data-toggle="modal" data-target="#acrylicsheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label> <h3><b>&ensp;&ensp;GAS KIT:</b></h3> </label>
<input type="number" name="gas_kit" value="<?php echo $panelCost['gas_kit']; ?>" placeholder="GAS KIT" id="gas_kit" required />
<button class="eye" data-toggle="modal" data-target="#gaskitsheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label> <h3><b>&ensp;&ensp;I-BOLT:</b></h3> </label>
<input type="number" name="i_bolt" value="<?php echo $panelCost['i_bolt']; ?>" placeholder="I-BOLT" id="i_bolt" required />
<button class="eye" data-toggle="modal" data-target="#iboltsheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label> <h3><b>&ensp;&ensp;BUS BAR/KG:</b></h3> </label>
<input type="number" name="bus_bar" value="<?php echo $panelCost['bus_bar']; ?>" placeholder="BUS BAR/KG" id="bus_bar" required />
<button class="eye" data-toggle="modal" data-target="#busbarsheet"><i class="fa fa-eye" aria-hidden="true"></i></button><br>

<label> <h3><b>CABLE DUCT:</b></h3> </label> <br>
<label>25x25:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['tf_tf']; ?>" name="tf_tf" placeholder="25x25" id="tf_tf" required />
<button class="eye" data-toggle="modal" data-target="#tftfsheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;25x40:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['tf_f']; ?>" name="tf_f" placeholder="25x40" id="tf_f" required />
<button class="eye" data-toggle="modal" data-target="#tffsheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;33x33:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['tt_tt']; ?>" name="tt_tt" placeholder="33x33" id="tt_tt" required />
<button class="eye" data-toggle="modal" data-target="#ttttsheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;40x40:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['f_f']; ?>" name="f_f" placeholder="40x40" id="f_f" required />
<button class="eye" data-toggle="modal" data-target="#ffsheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;40x60:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['f_s']; ?>" name="f_s" placeholder="40x60" id="f_s" required />
<button class="eye" data-toggle="modal" data-target="#fssheet"><i class="fa fa-eye" aria-hidden="true"></i></button><br>
<label>&ensp;60x40:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['s_f']; ?>" name="s_f" placeholder="60x40" id="s_f" required />
<button class="eye" data-toggle="modal" data-target="#shsheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;60x60:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['s_s']; ?>" name="s_s" placeholder="60x60" id="s_s" required />
<button class="eye" data-toggle="modal" data-target="#sssheets"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;80x80:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['e_e']; ?>" name="e_e" placeholder="80x80" id="e_e" required />
<button class="eye" data-toggle="modal" data-target="#eesheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<label>&ensp;100x100:</label>
<input type="number" style="width:11%" value="<?php echo $panelCost['h_h']; ?>" name="h_h" placeholder="100x100" id="h_h" required />
<button class="eye" data-toggle="modal" data-target="#hhsheet"><i class="fa fa-eye" aria-hidden="true"></i></button>
<?php
}
?>
<div>

<!-- Modal -->
<div class="modal" id="mssheet" tabindex="-1" role="dialog" aria-labelledby="mssheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mssheetLong"><b> MS SHEET</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="ms_sheet"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<!-- ss_sheet Modal -->
<div class="modal" id="sssheet" tabindex="-1" role="dialog" aria-labelledby="sssheetlong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sssheetlong"><b> SS SHEET</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="ss_sheet"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<!-- GI SHEET Modal -->
<div class="modal" id="gisheet" tabindex="-1" role="dialog" aria-labelledby="gisheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="gisheetLong"><b> SS SHEET</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="gi_sheet"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="h7032sheet" tabindex="-1" role="dialog" aria-labelledby="h7032sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="h7032sheetLong"><b> Depth(100-200):M/F7032</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="h_7032"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="h7035sheet" tabindex="-1" role="dialog" aria-labelledby="h7035sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="h7035sheetLong"><b> Depth(100-200):M/F7035</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="h_7035"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="tf7032sheet" tabindex="-1" role="dialog" aria-labelledby="tf7032sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tf7032sheetLong"><b> Depth(250-300):M/F7032</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="tf_7032"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="tf7035sheet" tabindex="-1" role="dialog" aria-labelledby="tf7035sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tf7035sheetLong"><b> Depth(250-300):M/F7035</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="tf_7035"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="thf7032sheet" tabindex="-1" role="dialog" aria-labelledby="thf7032sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="thf7032sheetLong"><b> Depth(350-450):M/F7032</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="thf_7032"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="thfshet7035" tabindex="-1" role="dialog" aria-labelledby="thfshet7035Long" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="thfshet7035Long"><b> Depth(350-450):M/F7035</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="thf_7035"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="f7032sheet" tabindex="-1" role="dialog" aria-labelledby="f7032sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="f7032sheetLong"><b> Depth(500-600):M/F7032</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="f_7032"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="f7035sheet" tabindex="-1" role="dialog" aria-labelledby="f7035sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="f7035sheetLong"><b> Depth(500-600):M/F7035</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="f_7035"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="s7032sheet" tabindex="-1" role="dialog" aria-labelledby="s7032sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="s7032sheetLong"><b> Depth(700-800):M/F7032</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="s_7032"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="s7035sheet" tabindex="-1" role="dialog" aria-labelledby="s7035sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="s7035sheetLong"><b> Depth(700-800):M/F7035</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="s_7035"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="n7032sheet" tabindex="-1" role="dialog" aria-labelledby="n7032sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="n7032sheetLong"><b> Depth(900-1200):M/F7032</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="n_7032"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="n7035sheet" tabindex="-1" role="dialog" aria-labelledby="n7035sheetLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="n7035sheetLong"><b> Depth(900-1200):M/F7035</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="n_7035"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="hl030sheet" tabindex="-1" role="dialog" aria-labelledby="hl030sheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="hl030sheetl"><b> HINGES(HL_030)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="hl_030"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="hl027sheet" tabindex="-1" role="dialog" aria-labelledby="hl027sheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="hl027sheetl"><b> HINGES(HL_027)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="hl_027"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>


<div class="modal" id="hl056sheet" tabindex="-1" role="dialog" aria-labelledby="hl056sheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="hl056sheetl"><b> HINGES(HL_056)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="hl_056"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="hl051sheet" tabindex="-1" role="dialog" aria-labelledby="hl051sheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="hl051sheetl"><b> HINGES(HL_051)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="hl_051"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="ms480sheet" tabindex="-1" role="dialog" aria-labelledby="ms480sheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ms480sheetl"><b> LOCK(MS-480)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="ms_480"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="ms408sheet" tabindex="-1" role="dialog" aria-labelledby="ms408sheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ms408sheetl"><b> LOCK(MS-408)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="ms_408"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="bnl22sheet" tabindex="-1" role="dialog" aria-labelledby="bnl22sheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bnl22sheetl"><b> LOCK(BNL-22)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="bnl_22"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="pl130sheet" tabindex="-1" role="dialog" aria-labelledby="pl130sheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pl130sheetl"><b> LOCK(PL-130)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="pl_130"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="pl150sheet" tabindex="-1" role="dialog" aria-labelledby="pl150sheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pl150sheetl"><b> LOCK(PL-150)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="pl_150"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="acrylicsheet" tabindex="-1" role="dialog" aria-labelledby="acrylicsheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="acrylicsheetl"><b> ACRYLIC SHEET</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="acrylic_sheet"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="gaskitsheet" tabindex="-1" role="dialog" aria-labelledby="gaskitsheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="gaskitsheetl"><b> GAS KIT</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="gas_kit"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="iboltsheet" tabindex="-1" role="dialog" aria-labelledby="iboltsheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="iboltsheetl"><b> I-BOLT:</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="i_bolt"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="busbarsheet" tabindex="-1" role="dialog" aria-labelledby="busbarsheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="busbarsheetl"><b> BUS BAR/KG</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="bus_bar"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="tftfsheet" tabindex="-1" role="dialog" aria-labelledby="tftfsheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tftfsheetl"><b> CABLE DUCT(25*25)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="tf_tf"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="tffsheet" tabindex="-1" role="dialog" aria-labelledby="tffsheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tffsheetl"><b> CABLE DUCT(25*40)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="tf_f"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="ttttsheet" tabindex="-1" role="dialog" aria-labelledby="ttttsheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ttttsheetl"><b> CABLE DUCT(33*33)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="tt_tt"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="ffsheet" tabindex="-1" role="dialog" aria-labelledby="ffsheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ffsheetl"><b> CABLE DUCT(40*40)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="f_f"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="fssheet" tabindex="-1" role="dialog" aria-labelledby="fssheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="fssheetl"><b> CABLE DUCT(40*60)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="f_s"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="shsheet" tabindex="-1" role="dialog" aria-labelledby="shsheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="shsheetl"><b> CABLE DUCT(60*40)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="s_f"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="sssheets" tabindex="-1" role="dialog" aria-labelledby="sssheetslong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sssheetslong"><b> CABLE DUCT(60*60)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="s_s"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="eesheet" tabindex="-1" role="dialog" aria-labelledby="eesheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eesheetl"><b> CABLE DUCT(80*80)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="e_e"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="hhsheet" tabindex="-1" role="dialog" aria-labelledby="hhsheetl" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="hhsheetl"><b> CABLE DUCT(100*100)</b></h5>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th>User</th>
    <th>Value</th>
    <th>Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM pc_rate_update  WHERE value_name="h_h"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['user']; ?></td>
    <td><?php echo $panelCost['updated_value']; ?></td>
    <td><?php echo $panelCost['updated_date']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>
</body>
<script src="javascripts.js"></script>
</html>