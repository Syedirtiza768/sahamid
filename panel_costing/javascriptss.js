$(document).ready(function () {
  $("#second_fd").hide();
  $("#third_fd").hide();
  $("#four_fd").hide();
  $("#five_fd").hide();
  $("#six_fd").hide();
  $("#seven_fd").hide();
  $("#eight_fd").hide();

  $("#two_bus_bar").hide();
  $("#three_bus_bar").hide();
  $("#four_bus_bar").hide();
  
});

document.getElementById('busbar_qty').onchange = function() {
  dimension=$("#bb_dimension").val();
  qty=$("#busbar_qty").val();
  var weight=0;
  var sleeve=0;
  if(dimension=="20*5"){weight= qty*0.3; sleeve= qty*43*1.5;}
  else if(dimension=="25*5"){weight= qty*0.4; sleeve= qty*48*1.5;}
  else if(dimension=="25*10"){ weight= qty*0.7; sleeve= qty*55*1.5; }
  else if(dimension=="30*5"){weight= qty*0.45;sleeve= qty*60*1.5;}
  else if(dimension=="30*10"){ weight= qty*0.85;sleeve= qty*65*1.5;}
  else if(dimension=="40*5"){weight= qty*0.6;sleeve= qty*65*1.5;}
  else if(dimension=="40*10"){weight= qty*1.1;sleeve= qty*75*1.5;}
  else if(dimension=="50*5"){ weight= qty*0.7;sleeve= qty*75*1.5;}
  else if(dimension=="50*10"){ weight= qty*1.4;sleeve= qty*85*1.5;}
  else if(dimension=="60*5"){weight= qty*0.85;sleeve= qty*85*1.5;}
  else if(dimension=="60*10"){weight= qty*1.7;sleeve= qty*95*1.5;}
  else if(dimension=="80*5"){weight= qty*1.1;sleeve= qty*110*1.5;}
  else if(dimension=="80*10"){weight= qty*2.2;sleeve= qty*115*1.5;}
  else if(dimension=="100*5"){weight= qty*1.4;sleeve= qty*125*1.5;}
  else if(dimension=="100*10"){weight= qty*2.8;sleeve= qty*135*1.5;}
  else if(dimension=="120*5"){weight= qty*1.67;sleeve= qty*145*1.5;}
  else if(dimension=="120*10"){weight= qty*3.3; sleeve= qty*155*1.5;}
  else if(dimension=="150*10"){ weight= qty*4.2;sleeve= qty*185*1.5;}
  else{
    alert("Please select dimension first.")
  }
 $("#busbar_weight").val(weight);
 $("#busbar_sleeve").val(sleeve);
};

document.getElementById('busbar_qty_two').onchange = function() {
  dimension=$("#bb_dimension_two").val();
  qty=$("#busbar_qty_two").val();
  var weight=0;
  var sleeve=0;
  if(dimension=="20*5"){weight= qty*0.3; sleeve= qty*43*1.5;}
  else if(dimension=="25*5"){weight= qty*0.4; sleeve= qty*48*1.5;}
  else if(dimension=="25*10"){ weight= qty*0.7; sleeve= qty*55*1.5; }
  else if(dimension=="30*5"){weight= qty*0.45;sleeve= qty*60*1.5;}
  else if(dimension=="30*10"){ weight= qty*0.85;sleeve= qty*65*1.5;}
  else if(dimension=="40*5"){weight= qty*0.6;sleeve= qty*65*1.5;}
  else if(dimension=="40*10"){weight= qty*1.1;sleeve= qty*75*1.5;}
  else if(dimension=="50*5"){ weight= qty*0.7;sleeve= qty*75*1.5;}
  else if(dimension=="50*10"){ weight= qty*1.4;sleeve= qty*85*1.5;}
  else if(dimension=="60*5"){weight= qty*0.85;sleeve= qty*85*1.5;}
  else if(dimension=="60*10"){weight= qty*1.7;sleeve= qty*95*1.5;}
  else if(dimension=="80*5"){weight= qty*1.1;sleeve= qty*110*1.5;}
  else if(dimension=="80*10"){weight= qty*2.2;sleeve= qty*115*1.5;}
  else if(dimension=="100*5"){weight= qty*1.4;sleeve= qty*125*1.5;}
  else if(dimension=="100*10"){weight= qty*2.8;sleeve= qty*135*1.5;}
  else if(dimension=="120*5"){weight= qty*1.67;sleeve= qty*145*1.5;}
  else if(dimension=="120*10"){weight= qty*3.3; sleeve= qty*155*1.5;}
  else if(dimension=="150*10"){ weight= qty*4.2;sleeve= qty*185*1.5;}
  else{
    alert("Please select dimension first.")
  }
 $("#busbar_weight_two").val(weight);
 $("#busbar_sleeve_two").val(sleeve);
};

document.getElementById('busbar_qty_three').onchange = function() {
  dimension=$("#bb_dimension_three").val();
  qty=$("#busbar_qty_three").val();
  var weight=0;
  var sleeve=0;
  if(dimension=="20*5"){weight= qty*0.3; sleeve= qty*43*1.5;}
  else if(dimension=="25*5"){weight= qty*0.4; sleeve= qty*48*1.5;}
  else if(dimension=="25*10"){ weight= qty*0.7; sleeve= qty*55*1.5; }
  else if(dimension=="30*5"){weight= qty*0.45;sleeve= qty*60*1.5;}
  else if(dimension=="30*10"){ weight= qty*0.85;sleeve= qty*65*1.5;}
  else if(dimension=="40*5"){weight= qty*0.6;sleeve= qty*65*1.5;}
  else if(dimension=="40*10"){weight= qty*1.1;sleeve= qty*75*1.5;}
  else if(dimension=="50*5"){ weight= qty*0.7;sleeve= qty*75*1.5;}
  else if(dimension=="50*10"){ weight= qty*1.4;sleeve= qty*85*1.5;}
  else if(dimension=="60*5"){weight= qty*0.85;sleeve= qty*85*1.5;}
  else if(dimension=="60*10"){weight= qty*1.7;sleeve= qty*95*1.5;}
  else if(dimension=="80*5"){weight= qty*1.1;sleeve= qty*110*1.5;}
  else if(dimension=="80*10"){weight= qty*2.2;sleeve= qty*115*1.5;}
  else if(dimension=="100*5"){weight= qty*1.4;sleeve= qty*125*1.5;}
  else if(dimension=="100*10"){weight= qty*2.8;sleeve= qty*135*1.5;}
  else if(dimension=="120*5"){weight= qty*1.67;sleeve= qty*145*1.5;}
  else if(dimension=="120*10"){weight= qty*3.3; sleeve= qty*155*1.5;}
  else if(dimension=="150*10"){ weight= qty*4.2;sleeve= qty*185*1.5;}
  else{
    alert("Please select dimension first.")
  }
 $("#busbar_weight_three").val(weight);
 $("#busbar_sleeve_three").val(sleeve);
};

document.getElementById('busbar_qty_four').onchange = function() {
  dimension=$("#bb_dimension_four").val();
  qty=$("#busbar_qty_four").val();
  var weight=0;
  var sleeve=0;
  if(dimension=="20*5"){weight= qty*0.3; sleeve= qty*43*1.5;}
  else if(dimension=="25*5"){weight= qty*0.4; sleeve= qty*48*1.5;}
  else if(dimension=="25*10"){ weight= qty*0.7; sleeve= qty*55*1.5; }
  else if(dimension=="30*5"){weight= qty*0.45;sleeve= qty*60*1.5;}
  else if(dimension=="30*10"){ weight= qty*0.85;sleeve= qty*65*1.5;}
  else if(dimension=="40*5"){weight= qty*0.6;sleeve= qty*65*1.5;}
  else if(dimension=="40*10"){weight= qty*1.1;sleeve= qty*75*1.5;}
  else if(dimension=="50*5"){ weight= qty*0.7;sleeve= qty*75*1.5;}
  else if(dimension=="50*10"){ weight= qty*1.4;sleeve= qty*85*1.5;}
  else if(dimension=="60*5"){weight= qty*0.85;sleeve= qty*85*1.5;}
  else if(dimension=="60*10"){weight= qty*1.7;sleeve= qty*95*1.5;}
  else if(dimension=="80*5"){weight= qty*1.1;sleeve= qty*110*1.5;}
  else if(dimension=="80*10"){weight= qty*2.2;sleeve= qty*115*1.5;}
  else if(dimension=="100*5"){weight= qty*1.4;sleeve= qty*125*1.5;}
  else if(dimension=="100*10"){weight= qty*2.8;sleeve= qty*135*1.5;}
  else if(dimension=="120*5"){weight= qty*1.67;sleeve= qty*145*1.5;}
  else if(dimension=="120*10"){weight= qty*3.3; sleeve= qty*155*1.5;}
  else if(dimension=="150*10"){ weight= qty*4.2;sleeve= qty*185*1.5;}
  else{
    alert("Please select dimension first.")
  }
 $("#busbar_weight_four").val(weight);
 $("#busbar_sleeve_four").val(sleeve);
};