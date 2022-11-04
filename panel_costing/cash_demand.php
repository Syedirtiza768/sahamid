<?php
require_once 'assets/config.php';
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
  <center>  <h1><b>Cash Demand</b></h1> </center>  
  <hr> 

   <div>
   <table>
  <tr>
    <th><h4><b>Expense Head</b></h4></th>
    <th><h4><b>Unit Cost</b></h4></th>
    <th><h4><b>Qty</b></h4></th>
    <th><h4><b>Budgeted Cost</b></h4></th>
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
  </tr>
  <tr>
    <td><b>Paint Cost</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
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
  </tr>
  <tr>
    <td><b>Travelling Cost</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
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
  </tr>
  <tr>
    <td><b>Food</b></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>MIS.EXP</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Over Time/Labour</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
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
  </tr>
  <tr>
    <td><b>Bus Bar & Salveeve</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
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
  </tr>
  <tr>
    <td><b>Granding Disk</b></td>
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
  </tr>
  <tr>
    <td><b>MS Rod</b></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>MS Sheets</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Cable</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>HINGES</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>LOCK</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>ACRYLIC SHEET</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b> GAS KIT</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Cable Duct</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>I-Bolt</b></td>
    <td>18648.4747969496</td>
    <td><input type="number" style="text-align:center" id="bending_qty" placeholder="Enter Qty"></td>
    <td>18648</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><b>Total Budget Cost</b></td>
    <td style="background-color:aqua"></td>
    <td style="background-color:aqua"></td>
    <td style="background-color:aqua">168126482322</td>
    <td style="background-color:aqua"></td>
    <td style="background-color:aqua"></td>
    <td style="background-color:aqua"></td>
  </tr>
</table>
</div>
</body>  
<script src="javascriptss.js"></script> 
</html>  