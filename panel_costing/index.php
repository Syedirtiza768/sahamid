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
<script src="links/alert.js"></script>
<link rel="stylesheet" href="stylish.css">

</head>
<body>
  <div class="container">
  <center>  <h1><b>Panel Costing</b></h1> </center>
  <hr>

  <input type="hidden" class="pc_id" >
    <div style="margin-left: 75%;
      position:fixed;
      right: 10%;" >
    <button  class="close" style="border:1px solid red; background-color:red; color:white" title="Close Tab" aria-label onclick="javascript:window.close()"></button>
  </div>
  <label> <h3><b>Panel Size:</b></h3> </label><br>
  <label>Height:</label>
  <input type="number" style="width:10%" name="pc_h" placeholder= "Height" id="pc_h" required>
  <label>&ensp;&ensp;Width:</label>
  <input type="number" style="width:10%" name="pc_w" placeholder= "Width" id="pc_w" required >
  <label>&ensp;&ensp;Depth:</label>
  <select style="width:10%" name="pc_d" id="pc_d">
    <option value="">Choose One</option>
    <option value="100">100</option>
    <option value="125">125</option>
    <option value="130">130</option>
    <option value="150">150</option>
    <option value="160">160</option>
    <option value="200">200</option>
    <option value="250">250</option>
    <option value="300">300</option>
    <option value="350">350</option>
    <option value="380">380</option>
    <option value="400">400</option>
    <option value="450">450</option>
    <option value="500">500</option>
    <option value="600">600</option>
    <option value="700">700</option>
    <option value="750">750</option>
    <option value="800">800</option>
    <option value="900">900</option>
    <option value="1000">1000</option>
    <option value="1100">1100</option>
    <option value="1200">1200</option>
  </select><br>

  <label> <h3><b>Panel Type:</b></h3> </label>
  <select style="width:10%" name="pc_type" id="pc_type">
    <option value="">Choose One</option>
    <option value="floor_mount">Floor Mount</option>
    <option value="wall_mount">Wall Mount</option>
  </select>

  <label> <h3><b>&ensp;&ensp;Conopy:</b></h3> </label>
  <select style="width:10%" name="conopy" id="conopy">
    <option value="">Choose One</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
  </select> <br>

  <label> <h3><b>Door # 01:</b></h3> </label><br>
  <label>Height:</label>
  <input type="number" style="width:10%" name="door1_h" placeholder= "Height" id="door1_h" required>
  <label>&ensp;&ensp;Width:</label>
  <input type="number" style="width:10%" name="door1_w" placeholder= "Width" id="door1_w" required >
  <label>&ensp;&ensp;Depth:</label>
  <input type="number" style="width:10%" name="door1_d" placeholder= "Depth" id="door1_d" required >
  <label>&ensp;&ensp;Cover Plate:</label>
  <select style="width:10%" name="cover_plate" id="door1_cp">
    <option value="">Choose One</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
  </select>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#second_fd').show()">Add More</button><br>

  <div id="second_fd">
  <label> <h3><b>Door # 02:</b></h3> </label><br>
  <label>Height:</label>
  <input type="number" style="width:10%" name="door2_h" placeholder= "Height" id="door2_h" required>
  <label>&ensp;&ensp;Width:</label>
  <input type="number" style="width:10%" name="door2_w" placeholder= "Width" id="door2_w" required >
  <label>&ensp;&ensp;Depth:</label>
  <input type="number" style="width:10%" name="door2_d" placeholder= "Depth" id="door2_d" required >
  <label>&ensp;&ensp;Cover Plate:</label>
  <select style="width:10%" name="cover_plate" id="door2_cp">
    <option value="">Choose One</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
  </select>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#third_fd').show()">Add More</button><br>
  </div>

  <div id="third_fd">
  <label> <h3><b>Door # 03:</b></h3> </label><br>
  <label>Height:</label>
  <input type="number" style="width:10%" name="door3_h" placeholder= "Height" id="door3_h" required>
  <label>&ensp;&ensp;Width:</label>
  <input type="number" style="width:10%" name="door3_w" placeholder= "Width" id="door3_w" required >
  <label>&ensp;&ensp;Depth:</label>
  <input type="number" style="width:10%" name="door3_d" placeholder= "Depth" id="door3_d" required >
  <label>&ensp;&ensp;Cover Plate:</label>
  <select style="width:10%" name="cover_plate" id="door3_cp">
    <option value="">Choose One</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
  </select>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#four_fd').show()">Add More</button><br>
  </div>

  <div id="four_fd">
  <label> <h3><b>Door # 04:</b></h3> </label><br>
  <label>Height:</label>
  <input type="number" style="width:10%" name="door4_h" placeholder= "Height" id="door4_h" required>
  <label>&ensp;&ensp;Width:</label>
  <input type="number" style="width:10%" name="door4_w" placeholder= "Width" id="door4_w" required >
  <label>&ensp;&ensp;Depth:</label>
  <input type="number" style="width:10%" name="door4_d" placeholder= "Depth" id="door4_d" required >
  <label>&ensp;&ensp;Cover Plate:</label>
  <select style="width:10%" name="cover_plate" id="door4_cp">
    <option value="">Choose One</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
  </select>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#five_fd').show()">Add More</button><br>
  </div>

  <div id="five_fd">
  <label> <h3><b>Door # 05:</b></h3> </label><br>
  <label>Height:</label>
  <input type="number" style="width:10%" name="door5_h" placeholder= "Height" id="door5_h" required>
  <label>&ensp;&ensp;Width:</label>
  <input type="number" style="width:10%" name="door5_w" placeholder= "Width" id="door5_w" required >
  <label>&ensp;&ensp;Depth:</label>
  <input type="number" style="width:10%" name="door5_d" placeholder= "Depth" id="door5_d" required >
  <label>&ensp;&ensp;Cover Plate:</label>
  <select style="width:10%" name="cover_plate" id="door5_cp">
    <option value="">Choose One</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
  </select>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#six_fd').show()">Add More</button><br>
  </div>

  <div id="six_fd">
  <label> <h3><b>Door # 06:</b></h3> </label><br>
  <label>Height:</label>
  <input type="number" style="width:10%" name="door6_h" placeholder= "Height" id="door6_h" required>
  <label>&ensp;&ensp;Width:</label>
  <input type="number" style="width:10%" name="door6_w" placeholder= "Width" id="door6_w" required >
  <label>&ensp;&ensp;Depth:</label>
  <input type="number" style="width:10%" name="door6_d" placeholder= "Depth" id="door6_d" required >
  <label>&ensp;&ensp;Cover Plate:</label>
  <select style="width:10%" name="cover_plate" id="door6_cp">
    <option value="">Choose One</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
  </select>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#seven_fd').show()">Add More</button><br>
  </div>

  <div id="seven_fd">
  <label> <h3><b>Door # 07:</b></h3> </label><br>
  <label>Height:</label>
  <input type="number" style="width:10%" name="door7_h" placeholder= "Height" id="door7_h" required>
  <label>&ensp;&ensp;Width:</label>
  <input type="number" style="width:10%" name="door7_w" placeholder= "Width" id="door7_w" required >
  <label>&ensp;&ensp;Depth:</label>
  <input type="number" style="width:10%" name="door7_d" placeholder= "Depth" id="door7_d" required >
  <label>&ensp;&ensp;Cover Plate:</label>
  <select style="width:10%" name="cover_plate" id="door7_cp">
    <option value="">Choose One</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
  </select>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#eight_fd').show()">Add More</button><br>
  </div>

  <div id="eight_fd">
  <label> <h3><b>Door # 08:</b></h3> </label><br>
  <label>Height:</label>
  <input type="number" style="width:10%" name="door8_h" placeholder= "Height" id="door8_h" required>
  <label>&ensp;&ensp;Width:</label>
  <input type="number" style="width:10%" name="door8_w" placeholder= "Width" id="door8_w" required >
  <label>&ensp;&ensp;Depth:</label>
  <input type="number" style="width:10%" name="door8_d" placeholder= "Depth" id="door8_d" required >
  <label>&ensp;&ensp;Cover Plate:</label>
  <select style="width:10%" name="cover_plate" id="door8_cp">
    <option value="">Choose One</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
  </select>
  </div><br>

  <label> <h3><b>Sheet Selection:</b></h3> </label>
  <select style="width:25%" name="sheet_selection" id="sheet_selection">
    <option value="">Choose One</option>
    <option value="ms_sheet">MS Sheet</option>
    <option value="ss_sheet">SS Sheet</option>
    <option value="gi_sheet">GI Sheet</option>
  </select> <br>


  <label> <h3><b>Bus Bar:</b></h3> </label> <br>
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension" id="bb_dimension">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty" placeholder= "Qty(Feet)" id="busbar_qty"  required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#two_bus_bar').show()">Add More</button><br>

  <div id="two_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_two" id="bb_dimension_two">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_two" placeholder= "Qty(Feet)" id="busbar_qty_two" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_two" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_two" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#three_bus_bar').show()">Add More</button><br>
  </div>

  <div id="three_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_three" id="bb_dimension_three">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_three" placeholder= "Qty(Feet)" id="busbar_qty_three" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_three" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_three" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#four_bus_bar').show()">Add More</button><br>
  </div>

  <div id="four_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_four" id="bb_dimension_four">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_four" placeholder= "Qty(Feet)" id="busbar_qty_four" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_four" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_four" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#five_bus_bar').show()">Add More</button><br>  
</div>

  <div id="five_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_five" id="bb_dimension_five">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_five" placeholder= "Qty(Feet)" id="busbar_qty_five" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_five" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_five" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#six_bus_bar').show()">Add More</button><br>
</div>

  <div id="six_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_six" id="bb_dimension_six">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_six" placeholder= "Qty(Feet)" id="busbar_qty_six" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_six" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_six" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#seven_bus_bar').show()">Add More</button><br>
</div>

  <div id="seven_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_seven" id="bb_dimension_seven">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_seven" placeholder= "Qty(Feet)" id="busbar_qty_seven" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_seven" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_seven" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#eight_bus_bar').show()">Add More</button><br>
</div>

  <div id="eight_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_eight" id="bb_dimension_eight">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_eight" placeholder= "Qty(Feet)" id="busbar_qty_eight" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_eight" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_eight" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#nine_bus_bar').show()">Add More</button><br>
</div>

  <div id="nine_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_nine" id="bb_dimension_nine">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_nine" placeholder= "Qty(Feet)" id="busbar_qty_nine" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_nine" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_nine" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#ten_bus_bar').show()">Add More</button><br>
</div>

  <div id="ten_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_ten" id="bb_dimension_ten">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_ten" placeholder= "Qty(Feet)" id="busbar_qty_ten" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_ten" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_ten" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#eleven_bus_bar').show()">Add More</button><br>
</div>

  <div id="eleven_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_eleven" id="bb_dimension_eleven">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_eleven" placeholder= "Qty(Feet)" id="busbar_qty_eleven" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_eleven" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_eleven" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#twelve_bus_bar').show()">Add More</button><br>
</div>

  <div id="twelve_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_twelve" id="bb_dimension_twelve">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_twelve" placeholder= "Qty(Feet)" id="busbar_qty_twelve" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_twelve" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_twelve" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#thirteen_bus_bar').show()">Add More</button><br>
</div>

  <div id="thirteen_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_thirteen" id="bb_dimension_thirteen">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_thirteen" placeholder= "Qty(Feet)" id="busbar_qty_thirteen" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_thirteen" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_thirteen" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#fourteen_bus_bar').show()">Add More</button><br>
</div>

  <div id="fourteen_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_fourteen" id="bb_dimension_fourteen">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_fourteen" placeholder= "Qty(Feet)" id="busbar_qty_fourteen" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_fourteen" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_fourteen" placeholder= "Sleeve Cost" readonly>
  <button id="addButton" class="fa fa-angle-down" onclick="$('#fifteen_bus_bar').show()">Add More</button><br>
</div>

  <div id="fifteen_bus_bar">
  <label>Dimensions:</label>
  <select style="width:10%" name="bb_dimension_fifteen" id="bb_dimension_fifteen">
    <option value="">Choose One</option>
    <option value="20*5">20*5</option>
    <option value="25*5">25*5</option>
    <option value="25*10">25*10</option>
    <option value="30*5">30*5</option>
    <option value="30*10">30*10</option>
    <option value="40*5">40*5</option>
    <option value="40*10">40*10</option>
    <option value="50*5">50*5</option>
    <option value="50*10">50*10</option>
    <option value="60*5">60*5</option>
    <option value="60*10">60*10</option>
    <option value="80*5">80*5</option>
    <option value="80*10">80*10</option>
    <option value="100*5">100*5</option>
    <option value="100*10">100*10</option>
    <option value="120*5">120*5</option>
    <option value="120*10">120*10</option>
    <option value="150*10">150*10</option>
  </select>
  <label>&ensp;&ensp;Qty(Feet):</label>
  <input type="number" style="width:10%" name="busbar_qty_fifteen" placeholder= "Qty(Feet)" id="busbar_qty_fifteen" required>
  <label>&ensp;&ensp;Weight:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_weight_fifteen" placeholder= "Weight" readonly>
  <label>&ensp;&ensp;Sleeve Cost:</label>
  <input type="number" style="width:10%; background:none; color:red" id="busbar_sleeve_fifteen" placeholder= "Sleeve Cost" readonly> <br>
  </div>
<br>

  <label> <h3><b>Paint Cost:</b></h3> </label>
  <select style="width:10%" name="paint_cost" id="paint_cost_model">
    <option value="">Choose One</option>
    <option value="7032">7032</option>
    <option value="7035">7035</option>
  </select> <br>

  <label> <h3><b>ACRYLIC SHEET:</b></h3> </label>
  <label>Quantity:</label>
  <input type="number" style="width:10%" name="ac_qty" placeholder= "Quantity" id="ac_qty" required>

  <label> <h3><b>&ensp;&ensp;Gas Kit:</b></h3> </label>
  <label>Quantity:</label>
  <input type="number" style="width:10%" name="gk_qty" placeholder= "Quantity" id="gk_qty" required>

  <label> <h3><b>&ensp;&ensp;I-Bolt:</b></h3> </label>
  <label>Quantity:</label>
  <input type="number" style="width:10%" name="ibolt_qty" placeholder= "Quantity" id="ibolt_qty" required> <br>

  <label> <h3><b>Hinges:</b></h3> </label> <br>
  <label>Model:</label>
  <select style="width:10%" name="hinges_model" id="hinges_model">
    <option value="">Choose One</option>
    <option value="hl_027">HL-027</option>
    <option value="hl_030">HL-030</option>
    <option value="hl_051">HL-051</option>
    <option value="hl_056">HL-056</option>
  </select>
  <label>&ensp;&ensp;Quantity:</label>
  <input type="number" style="width:10%" name="hinges_qty" placeholder= "Quantity" id="hinges_qty" required> <br>

  <label> <h3><b>Lock:</b></h3> </label> <br>
  <label>Model:</label>
  <select style="width:10%" name="lock_model" id="lock_model">
    <option value="">Choose One</option>
    <option value="ms_408">MS-408</option>
    <option value="ms_480">MS-480</option>
    <option value="bnl_22">BNL-22</option>
    <option value="pl_130">PL-130</option>
    <option value="pl_150">PL-150</option>
  </select>
  <label>&ensp;&ensp;Quantity:</label>
  <input type="number" style="width:10%" name="hinges_qty" placeholder= "Quantity" id="lock_qty" required> <br>

  <label> <h3><b>Cable Duct:</b></h3> </label> <br>
  <label>Model:</label>
  <select style="width:10%" name="cd_model" id="cd_model">
    <option value="">Choose One</option>
    <option value="25*25">25*25</option>
    <option value="25*40">25*40</option>
    <option value="33*33">33*33</option>
    <option value="40*40">40*40</option>
    <option value="40*60">40*60</option>
    <option value="60*40">60*40</option>
    <option value="60*60">60*60</option>
    <option value="80*80">80*80</option>
  </select>
  <label>&ensp;&ensp;Quantity:</label>
  <input type="number" style="width:10%" name="cd_qty" placeholder= "Quantity" id="cd_qty" required> <br>

  <label> <h3><b>Wiring Cost:</b></h3> </label>
  <input type="number" style="width:10%" name="wiring_cost" placeholder= "Quantity" id="wiring_cost" required>

  <label> <h3><b>&ensp;&ensp;Labour:</b></h3> </label>
  <input type="number" style="width:10%" name="labour" placeholder= "Quantity" id="labour" required>

  <label> <h3><b>&ensp;&ensp;MISC.EXP:</b></h3> </label>
  <input type="number" style="width:10%" name="misc_exp" placeholder= "Quantity" id="misc_exp" required>


  <label> <h3><b>&ensp;&ensp;Rent:</b></h3> </label>
  <input type="number" style="width:10%" name="rent" placeholder= "Rent" id="rent" required> <br>

  <label> <h3><b>14 SWG Increase By % :</b></h3> </label>
  <input type="number" style="width:10%" name="rent" value = "1" placeholder= "14 SWG %" id="Increase_percent_14" required> <br>

  <label> <h3><b>16 SWG Increase By % :</b></h3> </label>
  <input type="number" style="width:10%" name="rent" value = "1" placeholder= "16 SWG %" id="Increase_percent_16" required> <br>

  <label> <h3><b>18 SWG Increase By % :</b></h3> </label>
  <input type="number" style="width:10%" name="rent" value = "1" placeholder= "18 SWG %" id="Increase_percent_18" required> <br>

  <label> <h3><b>Sheet Value To Show In Cash demand:</b></h3> </label>
  <select style="width:25%" name="sheet_sheet" id="sheet_sheet_cd">
    <option value="">Choose One</option>
    <option value="ms_sheet">MS Sheet</option>
    <option value="ss_sheet">SS Sheet</option>
    <option value="gi_sheet">GI Sheet</option>
  </select> <br> <br><br>

  <button id="calculateButton" class="button" type="submit" value="Submit">Calculate All Panel Costing</button> <br><br>

  <label> <h3><b>Sheet Use:</b></h3> </label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Sheet Use" id="sheet_use" readonly> <br>

  <label> <h3><b>14 SWG Sheet:</b></h3> </label><br>
  <label>Sheet Weight</label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Sheet Weight" id="sheet_weight_14" readonly>
  <label>&ensp;&ensp;Sheet Cost</label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Sheet Cost" id="sheet_cost_14" readonly> <br>

  <label> <h3><b>16 SWG Sheet:</b></h3> </label><br>
  <label>Sheet Weight</label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Sheet Weight" id="sheet_weight_16" readonly>
  <label>&ensp;&ensp;Sheet Cost</label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Sheet Cost" id="sheet_cost_16" readonly> <br>

  <label> <h3><b>18 SWG Sheet:</b></h3> </label><br>
  <label>Sheet Weight</label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Sheet Weight" id="sheet_weight_18" readonly>
  <label>&ensp;&ensp;Sheet Cost</label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Sheet Cost" id="sheet_cost_18" readonly> <br>

  <label> <h3><b>Paint Cost:</b></h3> </label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Paint Cost" id="paint_cost" readonly>

  <label> <h3><b>&ensp;&ensp;Hinges Cost:</b></h3> </label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Hinges Cost" id="hinges_cost" readonly>

  <label> <h3><b>&ensp;&ensp;Lock Cost:</b></h3> </label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Lock Cost" id="lock_cost" readonly>

  <label> <h3><b>&ensp;&ensp;Acrylic Cost:</b></h3> </label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Acrylic Cost" id="acrylic_cost" readonly> <br>

  <label> <h3><b>Gas Kit Cost:</b></h3> </label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Gas Kit Cost" id="gk_cost" readonly>

  <label> <h3><b>&ensp;&ensp;I Bolt Cost:</b></h3> </label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "I Bolt Cost" id="ibolt_cost" readonly>

  <label> <h3><b>&ensp;&ensp;Cable Duct Cost:</b></h3> </label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Cable Duct Cost" id="cd_cost" readonly> <br>

  <label> <h3><b>Bus Bar:</b></h3> </label><br>
  <label>Weight/kg</label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Weight/kg" id="bbr_total_weight" readonly>
  <label>&ensp;&ensp;Total Cost</label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Total Cost" id="busbar_total_cost" readonly>
  <label>&ensp;&ensp;Total Sleeve Cost</label>
  <input type="number" style="width:10%; background:none; color:red"  placeholder= "Total Sleeve Cost" id="busbar_total_sleeve" readonly> <br>

  <label> <h3><b>14 SWG Sheet:</b></h3> </label><br>
  <label>Total Cost</label>
  <input type="number" style="width:14%; background:none; color:red"  placeholder= "Total Cost" id="swg_14_total" readonly>
  <label>&ensp;&ensp;Final Cost</label>
  <input type="number" style="width:14%; background:none; color:red"  placeholder= "Total Cost" id="swg_14_final_total" readonly> <br>

  <label> <h3><b>16 SWG Sheet:</b></h3> </label><br>
  <label>Total Cost</label>
  <input type="number" style="width:14%; background:none; color:red"  placeholder= "Total Cost" id="swg_16_total" readonly>
  <label>&ensp;&ensp;Final Cost</label>
  <input type="number" style="width:14%; background:none; color:red"  placeholder= "Total Cost" id="swg_16_final_total" readonly> <br>

  <label> <h3><b>18 SWG Sheet:</b></h3> </label><br>
  <label>Total Cost</label>
  <input type="number" style="width:14%; background:none; color:red"  placeholder= "Total Cost" id="swg_18_total" readonly>
  <label>&ensp;&ensp;Final Cost</label>
  <input type="number" style="width:14%; background:none; color:red"  placeholder= "Total Cost" id="swg_18_final_total" readonly> <br><br>

  <button id="save_exit" class="button" type="submit" value="Submit">Save And Exit</button> <br><br>
</div>
</body>
<script src="js_file.js"></script>
</html>
