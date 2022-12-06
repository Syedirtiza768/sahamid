
<?php
session_start();
require_once 'assets/config.php';
if (!empty($_POST['costNo'])) {
    $costNo = $_POST['costNo'];
    $_SESSION["pcContinue"] = $_POST['costNo'];
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
<link rel="stylesheet" href="cash_styles.css">


</head>
<body>
<div class="container">
<div style="margin-left: 75%;
      position:fixed;
      right: 8%;" >
    <button  class="close" style="border:1px solid red; background-color:red; color:white" title="Close Tab" aria-label onclick="javascript:window.close()"></button>
  </div>
  <center>  <h1><b>Cash Demand</b></h1> </center>
  <hr>

   <div>  <!-- Query to get previous saved value from database... -->
  <input type="hidden" id="pc_id" value="<?php echo $_SESSION['pcContinue'] ?>"  >
  <?php
$SQL = 'SELECT * FROM panel_costing  WHERE id="' . $_SESSION['pcContinue'] . '"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {

    $SQL = 'SELECT * FROM pc_cash_demand  WHERE pc_id="' . $_SESSION['pcContinue'] . '"';
    $result = mysqli_query($conn, $SQL);
    while ($cashDemand = mysqli_fetch_array($result)) {
        ?>
   <table>
  <tr>
    <th><h4><b>Expense Head</b></h4></th>
    <th><h4><b>Unit Cost</b></h4></th>
    <th><h4><b>Qty</b></h4></th>
    <th ><h4><b>Budgeted Cost</b></h4></th>
    <th><h4><b>Actual Cost</b></h4></th>
    <th><h4><b>Profit/Loss</b></h4></th>
    <th><h4><b>Already Paid</b></h4></th>
    <th><h4><b>Current Demand</b></h4></th>
    <th><h4><b>Total Cost</b></h4></th>
  </tr>
  <tr>
    <td><b>Bending Cost</b></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Paint Cost</b></td>
    <td><?php echo $panelCost['paintcost_total']; ?> </td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['paintcost_qty']; ?>" id="paintcost_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['paintcost_budget'] ?>" id="paintcost_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['paintcost_actual'] ?>" id="paintcost_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['paintcost_profit']; ?>"  id="paintcost_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>HDG Cost</b></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Travelling Cost</b></td>
    <td><?php echo $panelCost['rent']; ?></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['rent_qty']; ?>" id="rent_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['rent_budget'] ?>" id="rent_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['rent_actual'] ?>" id="rent_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['rent_profit']; ?>"  id="rent_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Purchase </b></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Food</b></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>MIS.EXP</b></td>
    <td><?php echo $panelCost['misc_exp']; ?></td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['misc_exp_qty']; ?>" id="misc_exp_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['misc_exp_budget'] ?>" id="misc_exp_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['misc_exp_actual'] ?>" id="misc_exp_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['misc_exp_profit']; ?>"  id="misc_exp_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>OverTime/Labour</b></td>
    <td><?php echo $panelCost['labour']; ?></td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['labour_qty']; ?>" id="labour_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['labour_budget'] ?>" id="labour_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['labour_actual'] ?>" id="labour_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['labour_profit']; ?>"  id="labour_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Threading</b></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
   <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>BusBar & Salveeve</b></td>
    <td><?php echo $panelCost['busbar_cost'] + $panelCost['busbar_sleeve']; ?></td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['bbr_sleeve_qty']; ?>" id="bbr_sleeve_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['bbr_sleeve_budget'] ?>" id="bbr_sleeve_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['bbr_sleeve_actual'] ?>" id="bbr_sleeve_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['bbr_sleeve_profit']; ?>"  id="bbr_sleeve_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Cutting Disk</b></td>
    <td></td>
    <td></td>
   <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Granding Disk</b></td>
    <td></td>
    <td></td>
   <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Buffing Disk</b></td>
    <td></td>
    <td></td>
   <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>MS Rod</b></td>
    <td></td>
    <td></td>
   <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <?php if($panelCost['sheet_sheet_cd'] == 'ms_sheet'){ ?>
  <tr>
    <td><b>MS Sheets</b></td>
    <td><?php echo $panelCost['14swg_sc']; ?></td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['ms_sheet_qty']; ?>" id="ms_sheet_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['ms_sheet_budget'] ?>" id="ms_sheet_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['ms_sheet_actual'] ?>" id="ms_sheet_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['ms_sheet_profit']; ?>"  id="ms_sheet_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <?php }
elseif($panelCost['sheet_sheet_cd'] == 'ss_sheet'){ ?>
<tr>
    <td><b>SS Sheets</b></td>
    <td><?php echo $panelCost['14swg_sc']; ?></td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['ms_sheet_qty']; ?>" id="ms_sheet_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['ms_sheet_budget'] ?>" id="ms_sheet_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['ms_sheet_actual'] ?>" id="ms_sheet_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['ms_sheet_profit']; ?>"  id="ms_sheet_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <?php }
elseif($panelCost['sheet_sheet_cd'] == 'gi_sheet'){ ?>
<tr>
    <td><b>GI Sheets</b></td>
    <td><?php echo $panelCost['14swg_sc']; ?></td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['ms_sheet_qty']; ?>" id="ms_sheet_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['ms_sheet_budget'] ?>" id="ms_sheet_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['ms_sheet_actual'] ?>" id="ms_sheet_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['ms_sheet_profit']; ?>"  id="ms_sheet_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <?php } ?>
  <tr>
    <td><b>Cable</b></td>
    <td><?php echo $panelCost['wiring']; ?></td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['cable_qty']; ?>" id="cable_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['cable_budget'] ?>" id="cable_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['cable_actual'] ?>" id="cable_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['cable_profit']; ?>"  id="cable_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>HINGES</b></td>
    <td><?php echo $panelCost['hinges_cost']; ?></td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['hinges_qty']; ?>" id="hinges_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['hinges_budget'] ?>" id="hinges_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['hinges_actual'] ?>" id="hinges_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['hinges_profit']; ?>"  id="hinges_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>LOCK</b></td>
    <td><?php echo $panelCost['lock_cost']; ?> </td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['lock_qty']; ?>" id="lock_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['lock_budget'] ?>" id="lock_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['lock_actual'] ?>" id="lock_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['lock_profit']; ?>"  id="lock_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>ACRYLIC SHEET</b></td>
    <td><?php echo $panelCost['acrylic_cost']; ?> </td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['acrylic_qty']; ?>" id="acrylic_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['acrylic_budget'] ?>" id="acrylic_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['acrylic_actual'] ?>" id="acrylic_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['acrylic_profit']; ?>"  id="acrylic_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b> GAS KIT</b></td>
    <td><?php echo $panelCost['gk_cost']; ?> </td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['gaskit_qty']; ?>" id="gaskit_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['gaskit_budget'] ?>" id="gaskit_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['gaskit_actual'] ?>" id="gaskit_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['gaskit_profit']; ?>"  id="gaskit_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Cable Duct</b></td>
    <td><?php echo $panelCost['cd_cost']; ?> </td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['cd_qty']; ?>" id="cd_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['cd_budget'] ?>" id="cd_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['cd_actual'] ?>" id="cd_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['cd_profit']; ?>"  id="cd_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>I-Bolt</b></td>
    <td><?php echo $panelCost['ibolt_cost']; ?> </td>
    <td><input type="number" style="text-align:center;  background:gainsboro" value = "<?php echo $cashDemand['ibolt_qty']; ?>" id="ibolt_qty" placeholder="Enter Qty"></td>
    <td><input type="number" style="text-align:center;" value = "<?php echo $cashDemand['ibolt_budget'] ?>" id="ibolt_budget" readonly></td>
    <td><input type="number" style="text-align:center;; background:gainsboro" value = "<?php echo $cashDemand['ibolt_actual'] ?>" id="ibolt_actual" placeholder="Enter Actual Price"></td>
    <td><input type="number" style="text-align:center;"  value = "<?php echo $cashDemand['ibolt_profit']; ?>"  id="ibolt_profit" readonly></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Total Budget Cost</b></td>
    <td style="background-color:aqua"></td>
    <td style="background-color:aqua"></td>
    <td style="background-color:aqua"><input type="number" style="background-color:aqua; text-align:center;" id="cashdemand_total" readonly></td>
    <td style="background-color:aqua"></td>
    <td style="background-color:aqua"></td>
    <td style="background-color:aqua"></td>
    <td style="background-color:aqua"></td>
    <td style="background-color:aqua"></td>
  </tr>
</table>
<?php }?>
<?php }?>

<label style="color:red"> <h3><b>Panel Edit Records:</b></h3> </label>
<button class="eye" data-toggle="modal" data-target="#pc_edit"><i class="fa fa-eye" aria-hidden="true"></i></button>

<!-- Modal -->
<div class="modal" id="pc_edit" tabindex="-1" role="dialog" aria-labelledby="pc_editLong" aria-hidden="true">
  <div class="modal-dialog" style="width:480px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" style="color:green" id="pc_editLong"><b> Panel Edit Records</b></h3>
      </div>
      <div class="modal-body">
      <table>
  <tr>
    <th style="width:500px; color:red">Description</th>
    <th style="width:110px; color:red">Date</th>
  </tr>
      <?php
$SQL = 'SELECT * FROM panelcostingmodifications  WHERE panel_id="' . $_SESSION['pcContinue'] . '"';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    ?>
  <tr>
    <td><?php echo $panelCost['pc_description']; ?></td>
    <td><?php echo $panelCost['updateDate']; ?></td>
  </tr>
  <?php }?>
</table>
      </div>
    </div>
  </div>
</div>

</div>
</body>

<script src="cd_javascripts.js"></script>
</html>
