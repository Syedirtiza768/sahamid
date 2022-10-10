var currentTab = 0;
document.addEventListener("DOMContentLoaded", function (event) {
  showTab(currentTab);
});
$(document).ready(function () {
  changeColor();
  $("#Matal").hide();
  $("#Stainless").hide();
  $("#Galvanized").hide();
  $("#pl_mf").hide();
  $("#pl_qty").hide();
  $("#h_mf").hide();
  $("#h_qty").hide();
  $("#cd_mf").hide();
  $("#cd_qty").hide();
  $("#pc_mf").hide();
  $("#second_bbs").hide();
  $("#third_bbs").hide();
  $("#second_fd").hide();
  $("#third_fd").hide();
  $("#fourth_fd").hide();
    $("#fifth_fd").hide();
    $("#sixth_fd").hide();
    $("#seventh_fd").hide();
    $("#eight_fd").hide();
    $("#ninth_fd").hide();
  $("#second_bd").hide();
  $("#third_bd").hide();
  $("#fourth_bd").hide();
  $("#fifth_bd").hide();
  $("#sixth_bd").hide();
  $("#seventh_bd").hide();
  $("#eightth_bd").hide();
  $("#ninth_bd").hide();
  $("#second_cp").hide();
  $("#third_cp").hide();
  $("#fourth_cp").hide();
  $("#fifth_cp").hide();
  $("#sixth_cp").hide();
  $("#seventh_cp").hide();
  $("#eighth_cp").hide();
  $("#ninth_cp").hide();
  $("#second_p").hide();
  $("#second_p_s").hide();
  $("#fourth_bbs").hide();
  $("#fifth_bbs").hide();
  $("#sixth_bbs").hide();
});

function showTab(n) {
  var x = document.getElementsByClassName("tab");
  x[n].style.display = "block";
  if (n == 0) {
    document.getElementById("prevBtn").style.display = "none";
    document.getElementById("prevBtn1").style.display = "none";
  } else {
    document.getElementById("prevBtn").style.display = "inline";
    document.getElementById("prevBtn1").style.display = "inline";
  }

  if (n == 31) {
    document.getElementById("nextBtn").style.display = "none";
    document.getElementById("nextBtn1").style.display = "none";
  } else {
    document.getElementById("nextBtn").style.display = "inline";
    document.getElementById("nextBtn1").style.display = "inline";
  }

  fixStepIndicator(n);
}

function viewTab(n) {
  let height = document.getElementById("ps_h").value;
  let width = document.getElementById("ps_w").value;
  let depth = document.getElementById("ps_d").value;
  if (depth != "" && width != "" && height != "") {
    var x = document.getElementsByClassName("tab");
    $("#second_bbs").hide();
    $("#second_fd").hide();
    $("#third_fd").hide();
    $("#fourth_fd").hide();
    $("#fifth_fd").hide();
    $("#sixth_fd").hide();
    $("#seventh_fd").hide();
    $("#eight_fd").hide();
    $("#ninth_fd").hide();
    $("#second_bd").hide();
    $("#third_bd").hide();
    $("#fourth_bd").hide();
  $("#fifth_bd").hide();
  $("#sixth_bd").hide();
  $("#seventh_bd").hide();
  $("#eightth_bd").hide();
  $("#ninth_bd").hide();
    $("#second_cp").hide();
    $("#third_cp").hide();
  $("#fourth_cp").hide();
  $("#fifth_cp").hide();
  $("#sixth_cp").hide();
  $("#seventh_cp").hide();
  $("#eighth_cp").hide();
  $("#ninth_cp").hide();
    $("#second_p").hide();
    $("#second_p_s").hide();
    $("#third_bbs").hide();
    $("#fourth_bbs").hide();
    $("#fifth_bbs").hide();
    $("#sixth_bbs").hide();
    x[currentTab].style.display = "none";
    currentTab = n;
    x[n].style.display = "block";
    if (n == 0) {
      document.getElementById("prevBtn").style.display = "none";
      document.getElementById("prevBtn1").style.display = "none";
    } else {
      document.getElementById("prevBtn").style.display = "inline";
      document.getElementById("prevBtn1").style.display = "inline";
    }

    if (n == 31) {
      document.getElementById("nextBtn").style.display = "none";
      document.getElementById("nextBtn1").style.display = "none";
    } else {
      document.getElementById("nextBtn").style.display = "inline";
      document.getElementById("nextBtn1").style.display = "inline";
    }
    fixStepIndicator(n);
  } else {
    Swal.fire("Please Enter All Values First");
  }
}

function nextPrev(n) {
  let height = document.getElementById("ps_h").value;
  let width = document.getElementById("ps_w").value;
  let depth = document.getElementById("ps_d").value;
  if (depth != "" && width != "" && height != "") {
    var x = document.getElementsByClassName("tab");
    $("#second_bbs").hide();
    $("#second_fd").hide();
    $("#third_fd").hide();
    $("#fourth_fd").hide();
    $("#fifth_fd").hide();
    $("#sixth_fd").hide();
    $("#seventh_fd").hide();
    $("#eight_fd").hide();
    $("#ninth_fd").hide();
    $("#second_bd").hide();
    $("#third_bd").hide();
    $("#fourth_bd").hide();
  $("#fifth_bd").hide();
  $("#sixth_bd").hide();
  $("#seventh_bd").hide();
  $("#eightth_bd").hide();
  $("#ninth_bd").hide();
    $("#second_cp").hide();
    $("#third_cp").hide();
  $("#fourth_cp").hide();
  $("#fifth_cp").hide();
  $("#sixth_cp").hide();
  $("#seventh_cp").hide();
  $("#eighth_cp").hide();
  $("#ninth_cp").hide(); 
    $("#second_p").hide();
    $("#second_p_s").hide();
    $("#third_bbs").hide();
    $("#fourth_bbs").hide();
    $("#fifth_bbs").hide();
    $("#sixth_bbs").hide();
    x[currentTab].style.display = "none";
    currentTab = currentTab + n;
    if (currentTab >= x.length) {
      // alert(currentTab);
      // document.getElementById("nextbtn").style.display = "none";
    } else {
      showTab(currentTab);
    }
  } else {
    Swal.fire("Please Enter All Values First");
  }
}

function fixStepIndicator(n) {
  var i,
    x = document.getElementsByClassName("step");
  for (i = 0; i < x.length; i++) {
    x[i].className = x[i].className.replace(" active", "");
  }
  x[n].className += " active";

  changeColor();
}

function changeColor() {
  let height = document.getElementById("ps_h").value;
  let width = document.getElementById("ps_w").value;
  let depth = document.getElementById("ps_d").value;
  if (depth != "" && width != "" && height != "") {
    $("#one").css("background-color", "rgb(13, 211, 102)");
  }

  var check = $(".fd_cal").val();
  if (check != "" && check != "0") {
    $("#two").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".bd_cal").val();
  if (check != "" && check != "0") {
    $("#three").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".sd_cal").val();
  if (check != "" && check != "0") {
    $("#four").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".vs_cal").val();
  if (check != "" && check != "0") {
    $("#five").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".hs_cal").val();
  if (check != "" && check != "0") {
    $("#six").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".hsp_cal").val();
  if (check != "" && check != "0") {
    $("#seven").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".cp_cal").val();
  if (check != "" && check != "0") {
    $("#eight").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".p_cal").val();
  if (check != "" && check != "0") {
    $("#nine").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".front_back_cal").val();
  if (check != "" && check != "0") {
    $("#ten").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".right_left_cal").val();
  if (check != "" && check != "0") {
    $("#eleven").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".vertical_inside_cal").val();
  if (check != "" && check != "0") {
    $("#twelve").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".horizonal_inside_cal").val();
  if (check != "" && check != "0") {
    $("#thirteen").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".Vertical_inside_piece_cal").val();
  if (check != "" && check != "0") {
    $("#fourteen").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".horizental_inside_piece_U_cal").val();
  if (check != "" && check != "0") {
    $("#fifteen").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".top_cal").val();
  if (check != "" && check != "0") {
    $("#sixteen").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".bottom_cal").val();
  if (check != "" && check != "0") {
    $("#seventeen").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".p_s_cal").val();
  if (check != "" && check != "0") {
    $("#eighteen").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".miscellaneous_cal").val();
  if (check != "" && check != "0") {
    $("#ninteen").css("background-color", "rgb(13, 211, 102)");
  }

  check = $("#sheet_type").val();
  if (check != "" && check != "0") {
    $("#twenty").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".first_bbr_weight").val();
  if (check != "" && check != "0") {
    $("#twenty_one").css("background-color", "rgb(13, 211, 102)");
  }

  check = document.getElementById("bus_bar_price").value;
  if (check != "" && check != "0") {
    $("#twenty_two").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".pl_cost").val();
  if (check != "" && check != "0") {
    $("#twenty_three").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".h_cost").val();
  if (check != "" && check != "0") {
    $("#twenty_four").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".as_cost").val();
  if (check != "" && check != "0") {
    $("#twenty_five").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".gk_cost").val();
  if (check != "" && check != "0") {
    $("#twenty_six").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".i_cost").val();
  if (check != "" && check != "0") {
    $("#twenty_seven").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".cd_cost").val();
  if (check != "" && check != "0") {
    $("#twenty_eight").css("background-color", "rgb(13, 211, 102)");
  }

  check = $(".pc_cost").val();
  if (check != "" && check != "0") {
    $("#twenty_nine").css("background-color", "rgb(13, 211, 102)");
  }

  let polish = $(".polish_cost").val();
  let rent = $(".rent_cost").val();
  let wc_cost = $(".wc_cost").val();
  let lc_cost = $(".lc_cost").val();
  let me_cost = $(".me_cost").val();
  if (
    polish != "" ||
    rent != "" ||
    wc_cost != "" ||
    lc_cost != "" ||
    me_cost != ""
  ) {
    $("#thirty").css("background-color", "rgb(13, 211, 102)");
  }


let cp_tsmg = $(".cp_tsmg").val();
let cp_fsmg = $(".cp_fsmg").val();
let cp_ssmg = $(".cp_ssmg").val();
let cp_esmg = $(".cp_esmg").val();
let cp_tysmg = $(".cp_tysmg").val();
let cp_mult_gauge = $(".cp_mult_gauge").val();
if (
  cp_tsmg != "" && cp_tsmg != "0" ||
  cp_fsmg != "" && cp_fsmg != "0" ||
  cp_ssmg != "" && cp_ssmg != "0" ||
  cp_esmg != "" && cp_esmg != "0" ||
  cp_tysmg != ""&& cp_tysmg != "0" ||
  cp_mult_gauge != "" && cp_mult_gauge != "0" 
) {
  $("#thirty_one").css("background-color", "rgb(13, 211, 102)");
}
}

$(document).on("click", "#buttonAlert", function () {
  let mlt_gauge_pr= $(".mlt_gauge_pr").val();
  let total_sleeve_cost = $(".total_sleeve_cost").val();
  if (mlt_gauge_pr == "" && total_sleeve_cost == "") {
    confirm("It seems have not calculated all panel cost are you sure to save and exit permanently?");
    if(confirm){
      let pannel_costing_id = $(".pannel_costing_id").val();
      $.ajax({
        url: "cost/done.php",
        method: "POST",
        data: {
          pannel_costing_id,
        },
        success: function (data) {
          self.close();
        },
      });
    }
    else{
    }

  }else {
    self.close();
  }
});

// For Pannel Size
$(document).on("click", "#ps-cost", function () {
  let height = document.getElementById("ps_h").value;
  let width = document.getElementById("ps_w").value;
  let depth = document.getElementById("ps_d").value;
  if (depth != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/ps.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        depth: depth,
      },
      success: function (data) {
        $(".pannel_costing_id").val(data);
      },
    });
    nextPrev(1);
  } else {
    Swal.fire("Please Enter All Values First");
  }
});

// For calcuation of front door
$(document).on("click", "#fd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("fd_h").value;
  let width = document.getElementById("fd_w").value;
  let qty = document.getElementById("fd_q").value;
  let sheet = document.getElementById("fd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/fd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".fd_cal").val(respData.square_feet);
        $(".fd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".fd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/fd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#second_fd").hide();
        $("#third_fd").hide();
        $("#fourth_fd").hide();
        $("#fifth_fd").hide();
        $("#sixth_fd").hide();
        $("#seventh_fd").hide();
        $("#eight_fd").hide();
        $("#ninth_fd").hide();

        if($(".s_fd_WBS").val() != "")
        {
          let height = document.getElementById("s_fd_h").value;
          let width = document.getElementById("s_fd_w").value;
          let qty = document.getElementById("s_fd_q").value;
          let sheet = document.getElementById("s_fd_sheet").value;
          let s_fd_cal = $(".s_fd_cal").val();
          let s_fd_WBS = $(".s_fd_WBS").val();

          $("#fd_h").val(height);
          $("#fd_w").val(width);
          $("#fd_q").val(qty);
          $("#fd_sheet").val(sheet);
          $(".fd_cal").val(s_fd_cal);
          $(".fd_WBS").val(s_fd_WBS);

          $("#s_fd_h").val("");
          $("#s_fd_w").val("");
          $("#s_fd_q").val("");
          $("#s_fd_sheet").val("");
          $(".s_fd_cal").val("");
          $(".s_fd_WBS").val("");               
        

        if($(".t_fd_WBS").val() != "")
        {
          let height = document.getElementById("t_fd_h").value;
          let width = document.getElementById("t_fd_w").value;
          let qty = document.getElementById("t_fd_q").value;
          let sheet = document.getElementById("t_fd_sheet").value;
          let t_fd_cal = $(".t_fd_cal").val();
          let t_fd_WBS = $(".t_fd_WBS").val();

          $("#s_fd_h").val(height);
          $("#s_fd_w").val(width);
          $("#s_fd_q").val(qty);
          $("#s_fd_sheet").val(sheet);
          $(".s_fd_cal").val(t_fd_cal);
          $(".s_fd_WBS").val(t_fd_WBS);

          $("#t_fd_h").val("");
          $("#t_fd_w").val("");
          $("#t_fd_q").val("");
          $("#t_fd_sheet").val("");
          $(".t_fd_cal").val("");
          $(".t_fd_WBS").val("");     

          $("#second_fd").show();          
        }

        if($(".f_fd_WBS").val() != "")
        {
          let height = document.getElementById("f_fd_h").value;
          let width = document.getElementById("f_fd_w").value;
          let qty = document.getElementById("f_fd_q").value;
          let sheet = document.getElementById("f_fd_sheet").value;
          let f_fd_cal = $(".f_fd_cal").val();
          let f_fd_WBS = $(".f_fd_WBS").val();

          $("#t_fd_h").val(height);
          $("#t_fd_w").val(width);
          $("#t_fd_q").val(qty);
          $("#t_fd_sheet").val(sheet);
          $(".t_fd_cal").val(f_fd_cal);
          $(".t_fd_WBS").val(f_fd_WBS);

          $("#f_fd_h").val("");
          $("#f_fd_w").val("");
          $("#f_fd_q").val("");
          $("#f_fd_sheet").val("");
          $(".f_fd_cal").val("");
          $(".f_fd_WBS").val("");

          $("#third_fd").show();           
        }

        if($(".fth_fd_WBS").val() != "")
        {
          let height = document.getElementById("fth_fd_h").value;
          let width = document.getElementById("fth_fd_w").value;
          let qty = document.getElementById("fth_fd_q").value;
          let sheet = document.getElementById("fth_fd_sheet").value;
          let fth_fd_cal = $(".fth_fd_cal").val();
          let fth_fd_WBS = $(".fth_fd_WBS").val();

          $("#f_fd_h").val(height);
          $("#f_fd_w").val(width);
          $("#f_fd_q").val(qty);
          $("#f_fd_sheet").val(sheet);
          $(".f_fd_cal").val(fth_fd_cal);
          $(".f_fd_WBS").val(fth_fd_WBS);

          $("#fth_fd_h").val("");
          $("#fth_fd_w").val("");
          $("#fth_fd_q").val("");
          $("#fth_fd_sheet").val("");
          $(".fth_fd_cal").val("");
          $(".fth_fd_WBS").val("");    
          
          $("#fourth_fd").show();
        }

        if($(".six_fd_WBS").val() != "")
        {
          let height = document.getElementById("six_fd_h").value;
          let width = document.getElementById("six_fd_w").value;
          let qty = document.getElementById("six_fd_q").value;
          let sheet = document.getElementById("six_fd_sheet").value;
          let six_fd_cal = $(".six_fd_cal").val();
          let six_fd_WBS = $(".six_fd_WBS").val();

          $("#fth_fd_h").val(height);
          $("#fth_fd_w").val(width);
          $("#fth_fd_q").val(qty);
          $("#fth_fd_sheet").val(sheet);
          $(".fth_fd_cal").val(six_fd_cal);
          $(".fth_fd_WBS").val(six_fd_WBS);

          $("#six_fd_h").val("");
          $("#six_fd_w").val("");
          $("#six_fd_q").val("");
          $("#six_fd_sheet").val("");
          $(".six_fd_cal").val("");
          $(".six_fd_WBS").val("");

          $("#fifth_fd").show();             
        }

        if($(".sth_fd_WBS").val() != "")
        {
          let height = document.getElementById("sth_fd_h").value;
          let width = document.getElementById("sth_fd_w").value;
          let qty = document.getElementById("sth_fd_q").value;
          let sheet = document.getElementById("sth_fd_sheet").value;
          let sth_fd_cal = $(".sth_fd_cal").val();
          let sth_fd_WBS = $(".sth_fd_WBS").val();

          $("#six_fd_h").val(height);
          $("#six_fd_w").val(width);
          $("#six_fd_q").val(qty);
          $("#six_fd_sheet").val(sheet);
          $(".six_fd_cal").val(sth_fd_cal);
          $(".six_fd_WBS").val(sth_fd_WBS);

          $("#sth_fd_h").val("");
          $("#sth_fd_w").val("");
          $("#sth_fd_q").val("");
          $("#sth_fd_sheet").val("");
          $(".sth_fd_cal").val("");
          $(".sth_fd_WBS").val("");    

          $("#sixth_fd").show();           
        }

        if($(".e_fd_WBS").val() != "")
        {
          let height = document.getElementById("e_fd_h").value;
          let width = document.getElementById("e_fd_w").value;
          let qty = document.getElementById("e_fd_q").value;
          let sheet = document.getElementById("e_fd_sheet").value;
          let e_fd_cal = $(".e_fd_cal").val();
          let e_fd_WBS = $(".e_fd_WBS").val();

          $("#sth_fd_h").val(height);
          $("#sth_fd_w").val(width);
          $("#sth_fd_q").val(qty);
          $("#sth_fd_sheet").val(sheet);
          $(".sth_fd_cal").val(e_fd_cal);
          $(".sth_fd_WBS").val(e_fd_WBS);

          $("#e_fd_h").val("");
          $("#e_fd_w").val("");
          $("#e_fd_q").val("");
          $("#e_fd_sheet").val("");
          $(".e_fd_cal").val("");
          $(".e_fd_WBS").val("");      

          $("#seventh_fd").show();         
        }

        if($(".n_fd_WBS").val() != "")
        {
          let height = document.getElementById("n_fd_h").value;
          let width = document.getElementById("n_fd_w").value;
          let qty = document.getElementById("n_fd_q").value;
          let sheet = document.getElementById("n_fd_sheet").value;
          let n_fd_cal = $(".n_fd_cal").val();
          let n_fd_WBS = $(".n_fd_WBS").val();

          $("#e_fd_h").val(height);
          $("#e_fd_w").val(width);
          $("#e_fd_q").val(qty);
          $("#e_fd_sheet").val(sheet);
          $(".e_fd_cal").val(n_fd_cal);
          $(".e_fd_WBS").val(n_fd_WBS);

          $("#n_fd_h").val("");
          $("#n_fd_w").val("");
          $("#n_fd_q").val("");
          $("#n_fd_sheet").val("");
          $(".n_fd_cal").val("");
          $(".n_fd_WBS").val("");     

          $("#eight_fd").show();          
        }
        }
        else{
        $("#fd_h").val("");
        $("#fd_w").val("");
        $("#fd_q").val("");
        $("#fd_sheet").val("");
        $(".fd_cal").val("");
        $(".fd_WBS").val("");

        }
      },
    });
});

$(document).on("click", "#s_fd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("s_fd_h").value;
  let width = document.getElementById("s_fd_w").value;
  let qty = document.getElementById("s_fd_q").value;
  let sheet = document.getElementById("s_fd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/s_fd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".s_fd_cal").val(respData.square_feet);
        $(".s_fd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".s_fd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/s_fd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#third_fd").hide();
        $("#fourth_fd").hide();
        $("#fifth_fd").hide();
        $("#sixth_fd").hide();
        $("#seventh_fd").hide();
        $("#eight_fd").hide();
        $("#ninth_fd").hide();
        if($(".t_fd_WBS").val() != "")
        {
          let height = document.getElementById("t_fd_h").value;
          let width = document.getElementById("t_fd_w").value;
          let qty = document.getElementById("t_fd_q").value;
          let sheet = document.getElementById("t_fd_sheet").value;
          let t_fd_cal = $(".t_fd_cal").val();
          let t_fd_WBS = $(".t_fd_WBS").val();

          $("#s_fd_h").val(height);
          $("#s_fd_w").val(width);
          $("#s_fd_q").val(qty);
          $("#s_fd_sheet").val(sheet);
          $(".s_fd_cal").val(t_fd_cal);
          $(".s_fd_WBS").val(t_fd_WBS);

          $("#t_fd_h").val("");
          $("#t_fd_w").val("");
          $("#t_fd_q").val("");
          $("#t_fd_sheet").val("");
          $(".t_fd_cal").val("");
          $(".t_fd_WBS").val("");               
        
        
        if($(".f_fd_WBS").val() != "")
        {
          let height = document.getElementById("f_fd_h").value;
          let width = document.getElementById("f_fd_w").value;
          let qty = document.getElementById("f_fd_q").value;
          let sheet = document.getElementById("f_fd_sheet").value;
          let f_fd_cal = $(".f_fd_cal").val();
          let f_fd_WBS = $(".f_fd_WBS").val();

          $("#t_fd_h").val(height);
          $("#t_fd_w").val(width);
          $("#t_fd_q").val(qty);
          $("#t_fd_sheet").val(sheet);
          $(".t_fd_cal").val(f_fd_cal);
          $(".t_fd_WBS").val(f_fd_WBS);

          $("#f_fd_h").val("");
          $("#f_fd_w").val("");
          $("#f_fd_q").val("");
          $("#f_fd_sheet").val("");
          $(".f_fd_cal").val("");
          $(".f_fd_WBS").val("");    
          
          $("#third_fd").show();
        }

        if($(".fth_fd_WBS").val() != "")
        {
          let height = document.getElementById("fth_fd_h").value;
          let width = document.getElementById("fth_fd_w").value;
          let qty = document.getElementById("fth_fd_q").value;
          let sheet = document.getElementById("fth_fd_sheet").value;
          let fth_fd_cal = $(".fth_fd_cal").val();
          let fth_fd_WBS = $(".fth_fd_WBS").val();

          $("#f_fd_h").val(height);
          $("#f_fd_w").val(width);
          $("#f_fd_q").val(qty);
          $("#f_fd_sheet").val(sheet);
          $(".f_fd_cal").val(fth_fd_cal);
          $(".f_fd_WBS").val(fth_fd_WBS);

          $("#fth_fd_h").val("");
          $("#fth_fd_w").val("");
          $("#fth_fd_q").val("");
          $("#fth_fd_sheet").val("");
          $(".fth_fd_cal").val("");
          $(".fth_fd_WBS").val("");    

          $("#fourth_fd").show();           
        }

        if($(".six_fd_WBS").val() != "")
        {
          let height = document.getElementById("six_fd_h").value;
          let width = document.getElementById("six_fd_w").value;
          let qty = document.getElementById("six_fd_q").value;
          let sheet = document.getElementById("six_fd_sheet").value;
          let six_fd_cal = $(".six_fd_cal").val();
          let six_fd_WBS = $(".six_fd_WBS").val();

          $("#fth_fd_h").val(height);
          $("#fth_fd_w").val(width);
          $("#fth_fd_q").val(qty);
          $("#fth_fd_sheet").val(sheet);
          $(".fth_fd_cal").val(six_fd_cal);
          $(".fth_fd_WBS").val(six_fd_WBS);

          $("#six_fd_h").val("");
          $("#six_fd_w").val("");
          $("#six_fd_q").val("");
          $("#six_fd_sheet").val("");
          $(".six_fd_cal").val("");
          $(".six_fd_WBS").val("");        
          
          $("#fifth_fd").show();
        }

        if($(".sth_fd_WBS").val() != "")
        {
          let height = document.getElementById("sth_fd_h").value;
          let width = document.getElementById("sth_fd_w").value;
          let qty = document.getElementById("sth_fd_q").value;
          let sheet = document.getElementById("sth_fd_sheet").value;
          let sth_fd_cal = $(".sth_fd_cal").val();
          let sth_fd_WBS = $(".sth_fd_WBS").val();

          $("#six_fd_h").val(height);
          $("#six_fd_w").val(width);
          $("#six_fd_q").val(qty);
          $("#six_fd_sheet").val(sheet);
          $(".six_fd_cal").val(sth_fd_cal);
          $(".six_fd_WBS").val(sth_fd_WBS);

          $("#sth_fd_h").val("");
          $("#sth_fd_w").val("");
          $("#sth_fd_q").val("");
          $("#sth_fd_sheet").val("");
          $(".sth_fd_cal").val("");
          $(".sth_fd_WBS").val("");   

          $("#sixth_fd").show();            
        }

        if($(".e_fd_WBS").val() != "")
        {
          let height = document.getElementById("e_fd_h").value;
          let width = document.getElementById("e_fd_w").value;
          let qty = document.getElementById("e_fd_q").value;
          let sheet = document.getElementById("e_fd_sheet").value;
          let e_fd_cal = $(".e_fd_cal").val();
          let e_fd_WBS = $(".e_fd_WBS").val();

          $("#sth_fd_h").val(height);
          $("#sth_fd_w").val(width);
          $("#sth_fd_q").val(qty);
          $("#sth_fd_sheet").val(sheet);
          $(".sth_fd_cal").val(e_fd_cal);
          $(".sth_fd_WBS").val(e_fd_WBS);

          $("#e_fd_h").val("");
          $("#e_fd_w").val("");
          $("#e_fd_q").val("");
          $("#e_fd_sheet").val("");
          $(".e_fd_cal").val("");
          $(".e_fd_WBS").val("");       

          $("#seventh_fd").show();        
        }

        if($(".n_fd_WBS").val() != "")
        {
          let height = document.getElementById("n_fd_h").value;
          let width = document.getElementById("n_fd_w").value;
          let qty = document.getElementById("n_fd_q").value;
          let sheet = document.getElementById("n_fd_sheet").value;
          let n_fd_cal = $(".n_fd_cal").val();
          let n_fd_WBS = $(".n_fd_WBS").val();

          $("#e_fd_h").val(height);
          $("#e_fd_w").val(width);
          $("#e_fd_q").val(qty);
          $("#e_fd_sheet").val(sheet);
          $(".e_fd_cal").val(n_fd_cal);
          $(".e_fd_WBS").val(n_fd_WBS);

          $("#n_fd_h").val("");
          $("#n_fd_w").val("");
          $("#n_fd_q").val("");
          $("#n_fd_sheet").val("");
          $(".n_fd_cal").val("");
          $(".n_fd_WBS").val("");       

          $("#eight_fd").show();        
        }
      }
        else{
          $("#s_fd_h").val("");
          $("#s_fd_w").val("");
          $("#s_fd_q").val("");
          $("#s_fd_sheet").val("");
          $(".s_fd_cal").val("");
          $(".s_fd_WBS").val("");
        }
      },
    });
});

$(document).on("click", "#t_fd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("t_fd_h").value;
  let width = document.getElementById("t_fd_w").value;
  let qty = document.getElementById("t_fd_q").value;
  let sheet = document.getElementById("t_fd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/t_fd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".t_fd_cal").val(respData.square_feet);
        $(".t_fd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".t_fd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/t_fd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#fourth_fd").hide();
        $("#fifth_fd").hide();
        $("#sixth_fd").hide();
        $("#seventh_fd").hide();
        $("#eight_fd").hide();
        $("#ninth_fd").hide();
        if($(".f_fd_WBS").val() != "")
        {
          let height = document.getElementById("f_fd_h").value;
          let width = document.getElementById("f_fd_w").value;
          let qty = document.getElementById("f_fd_q").value;
          let sheet = document.getElementById("f_fd_sheet").value;
          let f_fd_cal = $(".f_fd_cal").val();
          let f_fd_WBS = $(".f_fd_WBS").val();

          $("#t_fd_h").val(height);
          $("#t_fd_w").val(width);
          $("#t_fd_q").val(qty);
          $("#t_fd_sheet").val(sheet);
          $(".t_fd_cal").val(f_fd_cal);
          $(".t_fd_WBS").val(f_fd_WBS);

          $("#f_fd_h").val("");
          $("#f_fd_w").val("");
          $("#f_fd_q").val("");
          $("#f_fd_sheet").val("");
          $(".f_fd_cal").val("");
          $(".f_fd_WBS").val("");               
        
        if($(".fth_fd_WBS").val() != "")
        {
          let height = document.getElementById("fth_fd_h").value;
          let width = document.getElementById("fth_fd_w").value;
          let qty = document.getElementById("fth_fd_q").value;
          let sheet = document.getElementById("fth_fd_sheet").value;
          let fth_fd_cal = $(".fth_fd_cal").val();
          let fth_fd_WBS = $(".fth_fd_WBS").val();

          $("#f_fd_h").val(height);
          $("#f_fd_w").val(width);
          $("#f_fd_q").val(qty);
          $("#f_fd_sheet").val(sheet);
          $(".f_fd_cal").val(fth_fd_cal);
          $(".f_fd_WBS").val(fth_fd_WBS);

          $("#fth_fd_h").val("");
          $("#fth_fd_w").val("");
          $("#fth_fd_q").val("");
          $("#fth_fd_sheet").val("");
          $(".fth_fd_cal").val("");
          $(".fth_fd_WBS").val("");    

          $("#fourth_fd").show();                 
        }

        if($(".six_fd_WBS").val() != "")
        {
          let height = document.getElementById("six_fd_h").value;
          let width = document.getElementById("six_fd_w").value;
          let qty = document.getElementById("six_fd_q").value;
          let sheet = document.getElementById("six_fd_sheet").value;
          let six_fd_cal = $(".six_fd_cal").val();
          let six_fd_WBS = $(".six_fd_WBS").val();

          $("#fth_fd_h").val(height);
          $("#fth_fd_w").val(width);
          $("#fth_fd_q").val(qty);
          $("#fth_fd_sheet").val(sheet);
          $(".fth_fd_cal").val(six_fd_cal);
          $(".fth_fd_WBS").val(six_fd_WBS);

          $("#six_fd_h").val("");
          $("#six_fd_w").val("");
          $("#six_fd_q").val("");
          $("#six_fd_sheet").val("");
          $(".six_fd_cal").val("");
          $(".six_fd_WBS").val("");

          $("#fifth_fd").show();                
        }

        if($(".sth_fd_WBS").val() != "")
        {
          let height = document.getElementById("sth_fd_h").value;
          let width = document.getElementById("sth_fd_w").value;
          let qty = document.getElementById("sth_fd_q").value;
          let sheet = document.getElementById("sth_fd_sheet").value;
          let sth_fd_cal = $(".sth_fd_cal").val();
          let sth_fd_WBS = $(".sth_fd_WBS").val();

          $("#six_fd_h").val(height);
          $("#six_fd_w").val(width);
          $("#six_fd_q").val(qty);
          $("#six_fd_sheet").val(sheet);
          $(".six_fd_cal").val(sth_fd_cal);
          $(".six_fd_WBS").val(sth_fd_WBS);

          $("#sth_fd_h").val("");
          $("#sth_fd_w").val("");
          $("#sth_fd_q").val("");
          $("#sth_fd_sheet").val("");
          $(".sth_fd_cal").val("");
          $(".sth_fd_WBS").val("");    

          $("#sixth_fd").show();              
        }

        if($(".e_fd_WBS").val() != "")
        {
          let height = document.getElementById("e_fd_h").value;
          let width = document.getElementById("e_fd_w").value;
          let qty = document.getElementById("e_fd_q").value;
          let sheet = document.getElementById("e_fd_sheet").value;
          let e_fd_cal = $(".e_fd_cal").val();
          let e_fd_WBS = $(".e_fd_WBS").val();

          $("#sth_fd_h").val(height);
          $("#sth_fd_w").val(width);
          $("#sth_fd_q").val(qty);
          $("#sth_fd_sheet").val(sheet);
          $(".sth_fd_cal").val(e_fd_cal);
          $(".sth_fd_WBS").val(e_fd_WBS);

          $("#e_fd_h").val("");
          $("#e_fd_w").val("");
          $("#e_fd_q").val("");
          $("#e_fd_sheet").val("");
          $(".e_fd_cal").val("");
          $(".e_fd_WBS").val("");      

          $("#seventh_fd").show();               
        }

        if($(".n_fd_WBS").val() != "")
        {
          let height = document.getElementById("n_fd_h").value;
          let width = document.getElementById("n_fd_w").value;
          let qty = document.getElementById("n_fd_q").value;
          let sheet = document.getElementById("n_fd_sheet").value;
          let n_fd_cal = $(".n_fd_cal").val();
          let n_fd_WBS = $(".n_fd_WBS").val();

          $("#e_fd_h").val(height);
          $("#e_fd_w").val(width);
          $("#e_fd_q").val(qty);
          $("#e_fd_sheet").val(sheet);
          $(".e_fd_cal").val(n_fd_cal);
          $(".e_fd_WBS").val(n_fd_WBS);

          $("#n_fd_h").val("");
          $("#n_fd_w").val("");
          $("#n_fd_q").val("");
          $("#n_fd_sheet").val("");
          $(".n_fd_cal").val("");
          $(".n_fd_WBS").val("");     

          $("#eight_fd").show();              
        }
      }
        else{
          $("#t_fd_h").val("");
        $("#t_fd_w").val("");
        $("#t_fd_q").val("");
        $("#t_fd_sheet").val("");
        $(".t_fd_cal").val("");
        $(".t_fd_WBS").val("");
        }
      },
    });
});

$(document).on("click", "#f_fd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("f_fd_h").value;
  let width = document.getElementById("f_fd_w").value;
  let qty = document.getElementById("f_fd_q").value;
  let sheet = document.getElementById("f_fd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/f_fd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".f_fd_cal").val(respData.square_feet);
        $(".f_fd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".f_fd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/f_fd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#fifth_fd").hide();
        $("#sixth_fd").hide();
        $("#seventh_fd").hide();
        $("#eight_fd").hide();
        $("#ninth_fd").hide();
        if($(".fth_fd_WBS").val() != "")
        {
          let height = document.getElementById("fth_fd_h").value;
          let width = document.getElementById("fth_fd_w").value;
          let qty = document.getElementById("fth_fd_q").value;
          let sheet = document.getElementById("fth_fd_sheet").value;
          let fth_fd_cal = $(".fth_fd_cal").val();
          let fth_fd_WBS = $(".fth_fd_WBS").val();

          $("#f_fd_h").val(height);
          $("#f_fd_w").val(width);
          $("#f_fd_q").val(qty);
          $("#f_fd_sheet").val(sheet);
          $(".f_fd_cal").val(fth_fd_cal);
          $(".f_fd_WBS").val(fth_fd_WBS);

          $("#fth_fd_h").val("");
          $("#fth_fd_w").val("");
          $("#fth_fd_q").val("");
          $("#fth_fd_sheet").val("");
          $(".fth_fd_cal").val("");
          $(".fth_fd_WBS").val("");               
        

        if($(".six_fd_WBS").val() != "")
        {
          let height = document.getElementById("six_fd_h").value;
          let width = document.getElementById("six_fd_w").value;
          let qty = document.getElementById("six_fd_q").value;
          let sheet = document.getElementById("six_fd_sheet").value;
          let six_fd_cal = $(".six_fd_cal").val();
          let six_fd_WBS = $(".six_fd_WBS").val();

          $("#fth_fd_h").val(height);
          $("#fth_fd_w").val(width);
          $("#fth_fd_q").val(qty);
          $("#fth_fd_sheet").val(sheet);
          $(".fth_fd_cal").val(six_fd_cal);
          $(".fth_fd_WBS").val(six_fd_WBS);

          $("#six_fd_h").val("");
          $("#six_fd_w").val("");
          $("#six_fd_q").val("");
          $("#six_fd_sheet").val("");
          $(".six_fd_cal").val("");
          $(".six_fd_WBS").val("");

          $("#fifth_fd").show();                
        }

        if($(".sth_fd_WBS").val() != "")
        {
          let height = document.getElementById("sth_fd_h").value;
          let width = document.getElementById("sth_fd_w").value;
          let qty = document.getElementById("sth_fd_q").value;
          let sheet = document.getElementById("sth_fd_sheet").value;
          let sth_fd_cal = $(".sth_fd_cal").val();
          let sth_fd_WBS = $(".sth_fd_WBS").val();

          $("#six_fd_h").val(height);
          $("#six_fd_w").val(width);
          $("#six_fd_q").val(qty);
          $("#six_fd_sheet").val(sheet);
          $(".six_fd_cal").val(sth_fd_cal);
          $(".six_fd_WBS").val(sth_fd_WBS);

          $("#sth_fd_h").val("");
          $("#sth_fd_w").val("");
          $("#sth_fd_q").val("");
          $("#sth_fd_sheet").val("");
          $(".sth_fd_cal").val("");
          $(".sth_fd_WBS").val("");    

          $("#sixth_fd").show();              
        }

        if($(".e_fd_WBS").val() != "")
        {
          let height = document.getElementById("e_fd_h").value;
          let width = document.getElementById("e_fd_w").value;
          let qty = document.getElementById("e_fd_q").value;
          let sheet = document.getElementById("e_fd_sheet").value;
          let e_fd_cal = $(".e_fd_cal").val();
          let e_fd_WBS = $(".e_fd_WBS").val();

          $("#sth_fd_h").val(height);
          $("#sth_fd_w").val(width);
          $("#sth_fd_q").val(qty);
          $("#sth_fd_sheet").val(sheet);
          $(".sth_fd_cal").val(e_fd_cal);
          $(".sth_fd_WBS").val(e_fd_WBS);

          $("#e_fd_h").val("");
          $("#e_fd_w").val("");
          $("#e_fd_q").val("");
          $("#e_fd_sheet").val("");
          $(".e_fd_cal").val("");
          $(".e_fd_WBS").val("");      

          $("#seventh_fd").show();               
        }

        if($(".n_fd_WBS").val() != "")
        {
          let height = document.getElementById("n_fd_h").value;
          let width = document.getElementById("n_fd_w").value;
          let qty = document.getElementById("n_fd_q").value;
          let sheet = document.getElementById("n_fd_sheet").value;
          let n_fd_cal = $(".n_fd_cal").val();
          let n_fd_WBS = $(".n_fd_WBS").val();

          $("#e_fd_h").val(height);
          $("#e_fd_w").val(width);
          $("#e_fd_q").val(qty);
          $("#e_fd_sheet").val(sheet);
          $(".e_fd_cal").val(n_fd_cal);
          $(".e_fd_WBS").val(n_fd_WBS);

          $("#n_fd_h").val("");
          $("#n_fd_w").val("");
          $("#n_fd_q").val("");
          $("#n_fd_sheet").val("");
          $(".n_fd_cal").val("");
          $(".n_fd_WBS").val("");     

          $("#eight_fd").show();              
        }
      }
        else{
          $("#f_fd_h").val("");
        $("#f_fd_w").val("");
        $("#f_fd_q").val("");
        $("#f_fd_sheet").val("");
        $(".f_fd_cal").val("");
        $(".f_fd_WBS").val("");
        }
        
      },
    });
});


$(document).on("click", "#fth_fd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("fth_fd_h").value;
  let width = document.getElementById("fth_fd_w").value;
  let qty = document.getElementById("fth_fd_q").value;
  let sheet = document.getElementById("fth_fd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/fth_fd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".fth_fd_cal").val(respData.square_feet);
        $(".fth_fd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".fth_fd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/fth_fd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#sixth_fd").hide();
        $("#seventh_fd").hide();
        $("#eight_fd").hide();
        $("#ninth_fd").hide();
        if($(".six_fd_WBS").val() != "")
        {
          let height = document.getElementById("six_fd_h").value;
          let width = document.getElementById("six_fd_w").value;
          let qty = document.getElementById("six_fd_q").value;
          let sheet = document.getElementById("six_fd_sheet").value;
          let six_fd_cal = $(".six_fd_cal").val();
          let six_fd_WBS = $(".six_fd_WBS").val();

          $("#fth_fd_h").val(height);
          $("#fth_fd_w").val(width);
          $("#fth_fd_q").val(qty);
          $("#fth_fd_sheet").val(sheet);
          $(".fth_fd_cal").val(six_fd_cal);
          $(".fth_fd_WBS").val(six_fd_WBS);

          $("#six_fd_h").val("");
          $("#six_fd_w").val("");
          $("#six_fd_q").val("");
          $("#six_fd_sheet").val("");
          $(".six_fd_cal").val("");
          $(".six_fd_WBS").val("");

          $("#fifth_fd").show();                
        
        
        if($(".sth_fd_WBS").val() != "")
        {
          let height = document.getElementById("sth_fd_h").value;
          let width = document.getElementById("sth_fd_w").value;
          let qty = document.getElementById("sth_fd_q").value;
          let sheet = document.getElementById("sth_fd_sheet").value;
          let sth_fd_cal = $(".sth_fd_cal").val();
          let sth_fd_WBS = $(".sth_fd_WBS").val();

          $("#six_fd_h").val(height);
          $("#six_fd_w").val(width);
          $("#six_fd_q").val(qty);
          $("#six_fd_sheet").val(sheet);
          $(".six_fd_cal").val(sth_fd_cal);
          $(".six_fd_WBS").val(sth_fd_WBS);

          $("#sth_fd_h").val("");
          $("#sth_fd_w").val("");
          $("#sth_fd_q").val("");
          $("#sth_fd_sheet").val("");
          $(".sth_fd_cal").val("");
          $(".sth_fd_WBS").val("");    

          $("#sixth_fd").show();              
        }

        if($(".e_fd_WBS").val() != "")
        {
          let height = document.getElementById("e_fd_h").value;
          let width = document.getElementById("e_fd_w").value;
          let qty = document.getElementById("e_fd_q").value;
          let sheet = document.getElementById("e_fd_sheet").value;
          let e_fd_cal = $(".e_fd_cal").val();
          let e_fd_WBS = $(".e_fd_WBS").val();

          $("#sth_fd_h").val(height);
          $("#sth_fd_w").val(width);
          $("#sth_fd_q").val(qty);
          $("#sth_fd_sheet").val(sheet);
          $(".sth_fd_cal").val(e_fd_cal);
          $(".sth_fd_WBS").val(e_fd_WBS);

          $("#e_fd_h").val("");
          $("#e_fd_w").val("");
          $("#e_fd_q").val("");
          $("#e_fd_sheet").val("");
          $(".e_fd_cal").val("");
          $(".e_fd_WBS").val("");      

          $("#seventh_fd").show();               
        }

        if($(".n_fd_WBS").val() != "")
        {
          let height = document.getElementById("n_fd_h").value;
          let width = document.getElementById("n_fd_w").value;
          let qty = document.getElementById("n_fd_q").value;
          let sheet = document.getElementById("n_fd_sheet").value;
          let n_fd_cal = $(".n_fd_cal").val();
          let n_fd_WBS = $(".n_fd_WBS").val();

          $("#e_fd_h").val(height);
          $("#e_fd_w").val(width);
          $("#e_fd_q").val(qty);
          $("#e_fd_sheet").val(sheet);
          $(".e_fd_cal").val(n_fd_cal);
          $(".e_fd_WBS").val(n_fd_WBS);

          $("#n_fd_h").val("");
          $("#n_fd_w").val("");
          $("#n_fd_q").val("");
          $("#n_fd_sheet").val("");
          $(".n_fd_cal").val("");
          $(".n_fd_WBS").val("");     

          $("#eight_fd").show();              
        }
      }
        else{
         $("#fth_fd_h").val("");
        $("#fth_fd_w").val("");
        $("#fth_fd_q").val("");
        $("#fth_fd_sheet").val("");
        $(".fth_fd_cal").val("");
        $(".fth_fd_WBS").val("");
        }
      },
    });
});


$(document).on("click", "#six_fd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("six_fd_h").value;
  let width = document.getElementById("six_fd_w").value;
  let qty = document.getElementById("six_fd_q").value;
  let sheet = document.getElementById("six_fd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/six_fd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".six_fd_cal").val(respData.square_feet);
        $(".six_fd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".six_fd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/six_fd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#seventh_fd").hide();
        $("#eight_fd").hide();
        $("#ninth_fd").hide();
        if($(".sth_fd_WBS").val() != "")
        {
          let height = document.getElementById("sth_fd_h").value;
          let width = document.getElementById("sth_fd_w").value;
          let qty = document.getElementById("sth_fd_q").value;
          let sheet = document.getElementById("sth_fd_sheet").value;
          let sth_fd_cal = $(".sth_fd_cal").val();
          let sth_fd_WBS = $(".sth_fd_WBS").val();

          $("#six_fd_h").val(height);
          $("#six_fd_w").val(width);
          $("#six_fd_q").val(qty);
          $("#six_fd_sheet").val(sheet);
          $(".six_fd_cal").val(sth_fd_cal);
          $(".six_fd_WBS").val(sth_fd_WBS);

          $("#sth_fd_h").val("");
          $("#sth_fd_w").val("");
          $("#sth_fd_q").val("");
          $("#sth_fd_sheet").val("");
          $(".sth_fd_cal").val("");
          $(".sth_fd_WBS").val("");    

          $("#sixth_fd").show();              
        

        if($(".e_fd_WBS").val() != "")
        {
          let height = document.getElementById("e_fd_h").value;
          let width = document.getElementById("e_fd_w").value;
          let qty = document.getElementById("e_fd_q").value;
          let sheet = document.getElementById("e_fd_sheet").value;
          let e_fd_cal = $(".e_fd_cal").val();
          let e_fd_WBS = $(".e_fd_WBS").val();

          $("#sth_fd_h").val(height);
          $("#sth_fd_w").val(width);
          $("#sth_fd_q").val(qty);
          $("#sth_fd_sheet").val(sheet);
          $(".sth_fd_cal").val(e_fd_cal);
          $(".sth_fd_WBS").val(e_fd_WBS);

          $("#e_fd_h").val("");
          $("#e_fd_w").val("");
          $("#e_fd_q").val("");
          $("#e_fd_sheet").val("");
          $(".e_fd_cal").val("");
          $(".e_fd_WBS").val("");      

          $("#seventh_fd").show();               
        }

        if($(".n_fd_WBS").val() != "")
        {
          let height = document.getElementById("n_fd_h").value;
          let width = document.getElementById("n_fd_w").value;
          let qty = document.getElementById("n_fd_q").value;
          let sheet = document.getElementById("n_fd_sheet").value;
          let n_fd_cal = $(".n_fd_cal").val();
          let n_fd_WBS = $(".n_fd_WBS").val();

          $("#e_fd_h").val(height);
          $("#e_fd_w").val(width);
          $("#e_fd_q").val(qty);
          $("#e_fd_sheet").val(sheet);
          $(".e_fd_cal").val(n_fd_cal);
          $(".e_fd_WBS").val(n_fd_WBS);

          $("#n_fd_h").val("");
          $("#n_fd_w").val("");
          $("#n_fd_q").val("");
          $("#n_fd_sheet").val("");
          $(".n_fd_cal").val("");
          $(".n_fd_WBS").val("");     

          $("#eight_fd").show();              
        }
      }
        else{
          $("#six_fd_h").val("");
          $("#six_fd_w").val("");
          $("#six_fd_q").val("");
          $("#six_fd_sheet").val("");
          $(".six_fd_cal").val("");
          $(".six_fd_WBS").val("");
        }
        
      },
    });
});


$(document).on("click", "#sth_fd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("sth_fd_h").value;
  let width = document.getElementById("sth_fd_w").value;
  let qty = document.getElementById("sth_fd_q").value;
  let sheet = document.getElementById("sth_fd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/sth_fd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".sth_fd_cal").val(respData.square_feet);
        $(".sth_fd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".sth_fd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/sth_fd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#eight_fd").hide();
        $("#ninth_fd").hide();
        if($(".e_fd_WBS").val() != "")
        {
          let height = document.getElementById("e_fd_h").value;
          let width = document.getElementById("e_fd_w").value;
          let qty = document.getElementById("e_fd_q").value;
          let sheet = document.getElementById("e_fd_sheet").value;
          let e_fd_cal = $(".e_fd_cal").val();
          let e_fd_WBS = $(".e_fd_WBS").val();

          $("#sth_fd_h").val(height);
          $("#sth_fd_w").val(width);
          $("#sth_fd_q").val(qty);
          $("#sth_fd_sheet").val(sheet);
          $(".sth_fd_cal").val(e_fd_cal);
          $(".sth_fd_WBS").val(e_fd_WBS);

          $("#e_fd_h").val("");
          $("#e_fd_w").val("");
          $("#e_fd_q").val("");
          $("#e_fd_sheet").val("");
          $(".e_fd_cal").val("");
          $(".e_fd_WBS").val("");      

          $("#seventh_fd").show();               
        
        
        if($(".n_fd_WBS").val() != "")
        {
          let height = document.getElementById("n_fd_h").value;
          let width = document.getElementById("n_fd_w").value;
          let qty = document.getElementById("n_fd_q").value;
          let sheet = document.getElementById("n_fd_sheet").value;
          let n_fd_cal = $(".n_fd_cal").val();
          let n_fd_WBS = $(".n_fd_WBS").val();

          $("#e_fd_h").val(height);
          $("#e_fd_w").val(width);
          $("#e_fd_q").val(qty);
          $("#e_fd_sheet").val(sheet);
          $(".e_fd_cal").val(n_fd_cal);
          $(".e_fd_WBS").val(n_fd_WBS);

          $("#n_fd_h").val("");
          $("#n_fd_w").val("");
          $("#n_fd_q").val("");
          $("#n_fd_sheet").val("");
          $(".n_fd_cal").val("");
          $(".n_fd_WBS").val("");     

          $("#eight_fd").show();              
        }
      }
        else{
          $("#sth_fd_h").val("");
          $("#sth_fd_w").val("");
          $("#sth_fd_q").val("");
          $("#sth_fd_sheet").val("");
          $(".sth_fd_cal").val("");
          $(".sth_fd_WBS").val("");
        }
        
      },
    });
});


$(document).on("click", "#e_fd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("e_fd_h").value;
  let width = document.getElementById("e_fd_w").value;
  let qty = document.getElementById("e_fd_q").value;
  let sheet = document.getElementById("e_fd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/e_fd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".e_fd_cal").val(respData.square_feet);
        $(".e_fd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".e_fd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/e_fd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#ninth_fd").hide();
        if($(".n_fd_WBS").val() != "")
        {
          let height = document.getElementById("n_fd_h").value;
          let width = document.getElementById("n_fd_w").value;
          let qty = document.getElementById("n_fd_q").value;
          let sheet = document.getElementById("n_fd_sheet").value;
          let n_fd_cal = $(".n_fd_cal").val();
          let n_fd_WBS = $(".n_fd_WBS").val();

          $("#e_fd_h").val(height);
          $("#e_fd_w").val(width);
          $("#e_fd_q").val(qty);
          $("#e_fd_sheet").val(sheet);
          $(".e_fd_cal").val(n_fd_cal);
          $(".e_fd_WBS").val(n_fd_WBS);

          $("#n_fd_h").val("");
          $("#n_fd_w").val("");
          $("#n_fd_q").val("");
          $("#n_fd_sheet").val("");
          $(".n_fd_cal").val("");
          $(".n_fd_WBS").val("");     

          $("#eight_fd").show();              
        }
      
        else{
          $("#e_fd_h").val("");
        $("#e_fd_w").val("");
        $("#e_fd_q").val("");
        $("#e_fd_sheet").val("");
        $(".e_fd_cal").val("");
        $(".e_fd_WBS").val("");
        }
        
      },
    });
});


$(document).on("click", "#n_fd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("n_fd_h").value;
  let width = document.getElementById("n_fd_w").value;
  let qty = document.getElementById("n_fd_q").value;
  let sheet = document.getElementById("n_fd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/n_fd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".n_fd_cal").val(respData.square_feet);
        $(".n_fd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".n_fd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/n_fd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#n_fd_h").val("");
        $("#n_fd_w").val("");
        $("#n_fd_q").val("");
        $("#n_fd_sheet").val("");
        $(".n_fd_cal").val("");
        $(".n_fd_WBS").val("");
      },
    });
});


// For calcuation of back door
$(document).on("click", "#bd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("bd_h").value;
  let width = document.getElementById("bd_w").value;
  let qty = document.getElementById("bd_q").value;
  let sheet = document.getElementById("bd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/bd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".bd_cal").val(respData.square_feet);
        $(".bd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".bd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();

    $.ajax({
      url: "cost/bd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#second_bd").hide();
        $("#third_bd").hide();
        $("#fourth_bd").hide();
        $("#fifth_bd").hide();
        $("#sixth_bd").hide();
        $("#seventh_bd").hide();
        $("#eightth_bd").hide();
        $("#ninth_bd").hide();
        if($(".s_bd_WBS").val() != "")
        {
          let height = document.getElementById("s_bd_h").value;
          let width = document.getElementById("s_bd_w").value;
          let qty = document.getElementById("s_bd_q").value;
          let sheet = document.getElementById("s_bd_sheet").value;
          let s_bd_cal = $(".s_bd_cal").val();
          let s_bd_WBS = $(".s_bd_WBS").val();

          $("#bd_h").val(height);
          $("#bd_w").val(width);
          $("#bd_q").val(qty);
          $("#bd_sheet").val(sheet);
          $(".bd_cal").val(s_bd_cal);
          $(".bd_WBS").val(s_bd_WBS);

          $("#s_bd_h").val("");
          $("#s_bd_w").val("");
          $("#s_bd_q").val("");
          $("#s_bd_sheet").val("");
          $(".s_bd_cal").val("");
          $(".s_bd_WBS").val("");               
        

        if($(".t_bd_WBS").val() != "")
        {
          let height = document.getElementById("t_bd_h").value;
          let width = document.getElementById("t_bd_w").value;
          let qty = document.getElementById("t_bd_q").value;
          let sheet = document.getElementById("t_bd_sheet").value;
          let t_bd_cal = $(".t_bd_cal").val();
          let t_bd_WBS = $(".t_bd_WBS").val();

          $("#s_bd_h").val(height);
          $("#s_bd_w").val(width);
          $("#s_bd_q").val(qty);
          $("#s_bd_sheet").val(sheet);
          $(".s_bd_cal").val(t_bd_cal);
          $(".s_bd_WBS").val(t_bd_WBS);

          $("#t_bd_h").val("");
          $("#t_bd_w").val("");
          $("#t_bd_q").val("");
          $("#t_bd_sheet").val("");
          $(".t_bd_cal").val("");
          $(".t_bd_WBS").val("");  
          $("#second_bd").show();
        }

        if($(".f_bd_WBS").val() != "")
        {
          let height = document.getElementById("f_bd_h").value;
          let width = document.getElementById("f_bd_w").value;
          let qty = document.getElementById("f_bd_q").value;
          let sheet = document.getElementById("f_bd_sheet").value;
          let f_bd_cal = $(".f_bd_cal").val();
          let f_bd_WBS = $(".f_bd_WBS").val();

          $("#t_bd_h").val(height);
          $("#t_bd_w").val(width);
          $("#t_bd_q").val(qty);
          $("#t_bd_sheet").val(sheet);
          $(".t_bd_cal").val(f_bd_cal);
          $(".t_bd_WBS").val(f_bd_WBS);

          $("#f_bd_h").val("");
          $("#f_bd_w").val("");
          $("#f_bd_q").val("");
          $("#f_bd_sheet").val("");
          $(".f_bd_cal").val("");
          $(".f_bd_WBS").val(""); 

          $("#third_bd").show();
        }

        if($(".fth_bd_WBS").val() != "")
        {
          let height = document.getElementById("fth_bd_h").value;
          let width = document.getElementById("fth_bd_w").value;
          let qty = document.getElementById("fth_bd_q").value;
          let sheet = document.getElementById("fth_bd_sheet").value;
          let fth_bd_cal = $(".fth_bd_cal").val();
          let fth_bd_WBS = $(".fth_bd_WBS").val();

          $("#f_bd_h").val(height);
          $("#f_bd_w").val(width);
          $("#f_bd_q").val(qty);
          $("#f_bd_sheet").val(sheet);
          $(".f_bd_cal").val(fth_bd_cal);
          $(".f_bd_WBS").val(fth_bd_WBS);

          $("#fth_bd_h").val("");
          $("#fth_bd_w").val("");
          $("#fth_bd_q").val("");
          $("#fth_bd_sheet").val("");
          $(".fth_bd_cal").val("");
          $(".fth_bd_WBS").val("");     

          $("#fourth_bd").show();          
        }

        if($(".six_bd_WBS").val() != "")
        {
          let height = document.getElementById("six_bd_h").value;
          let width = document.getElementById("six_bd_w").value;
          let qty = document.getElementById("six_bd_q").value;
          let sheet = document.getElementById("six_bd_sheet").value;
          let six_bd_cal = $(".six_bd_cal").val();
          let six_bd_WBS = $(".six_bd_WBS").val();

          $("#fth_bd_h").val(height);
          $("#fth_bd_w").val(width);
          $("#fth_bd_q").val(qty);
          $("#fth_bd_sheet").val(sheet);
          $(".fth_bd_cal").val(six_bd_cal);
          $(".fth_bd_WBS").val(six_bd_WBS);

          $("#six_bd_h").val("");
          $("#six_bd_w").val("");
          $("#six_bd_q").val("");
          $("#six_bd_sheet").val("");
          $(".six_bd_cal").val("");
          $(".six_bd_WBS").val("");  

          $("#fifth_bd").show();             
        }

        if($(".sth_bd_WBS").val() != "")
        {
          let height = document.getElementById("sth_bd_h").value;
          let width = document.getElementById("sth_bd_w").value;
          let qty = document.getElementById("sth_bd_q").value;
          let sheet = document.getElementById("sth_bd_sheet").value;
          let sth_bd_cal = $(".sth_bd_cal").val();
          let sth_bd_WBS = $(".sth_bd_WBS").val();

          $("#six_bd_h").val(height);
          $("#six_bd_w").val(width);
          $("#six_bd_q").val(qty);
          $("#six_bd_sheet").val(sheet);
          $(".six_bd_cal").val(sth_bd_cal);
          $(".six_bd_WBS").val(sth_bd_WBS);

          $("#sth_bd_h").val("");
          $("#sth_bd_w").val("");
          $("#sth_bd_q").val("");
          $("#sth_bd_sheet").val("");
          $(".sth_bd_cal").val("");
          $(".sth_bd_WBS").val(""); 

          $("#sixth_bd").show();         
        }

        if($(".e_bd_WBS").val() != "")
        {
          let height = document.getElementById("e_bd_h").value;
          let width = document.getElementById("e_bd_w").value;
          let qty = document.getElementById("e_bd_q").value;
          let sheet = document.getElementById("e_bd_sheet").value;
          let e_bd_cal = $(".e_bd_cal").val();
          let e_bd_WBS = $(".e_bd_WBS").val();

          $("#sth_bd_h").val(height);
          $("#sth_bd_w").val(width);
          $("#sth_bd_q").val(qty);
          $("#sth_bd_sheet").val(sheet);
          $(".sth_bd_cal").val(e_bd_cal);
          $(".sth_bd_WBS").val(e_bd_WBS);

          $("#e_bd_h").val("");
          $("#e_bd_w").val("");
          $("#e_bd_q").val("");
          $("#e_bd_sheet").val("");
          $(".e_bd_cal").val("");
          $(".e_bd_WBS").val("");      

          $("#seventh_bd").show();         
        }

        if($(".n_bd_WBS").val() != "")
        {
          let height = document.getElementById("n_bd_h").value;
          let width = document.getElementById("n_bd_w").value;
          let qty = document.getElementById("n_bd_q").value;
          let sheet = document.getElementById("n_bd_sheet").value;
          let n_bd_cal = $(".n_bd_cal").val();
          let n_bd_WBS = $(".n_bd_WBS").val();

          $("#e_bd_h").val(height);
          $("#e_bd_w").val(width);
          $("#e_bd_q").val(qty);
          $("#e_bd_sheet").val(sheet);
          $(".e_bd_cal").val(n_bd_cal);
          $(".e_bd_WBS").val(n_bd_WBS);

          $("#n_bd_h").val("");
          $("#n_bd_w").val("");
          $("#n_bd_q").val("");
          $("#n_bd_sheet").val("");
          $(".n_bd_cal").val("");
          $(".n_bd_WBS").val("");    

         $("#eightth_bd").show();          
        }
        }
        else{
        $("#bd_h").val("");
        $("#bd_w").val("");
        $("#bd_q").val("");
        $("#bd_sheet").val("");
        $(".bd_cal").val("");
        $(".bd_WBS").val("");
        }
      },
    });
});

$(document).on("click", "#s_bd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("s_bd_h").value;
  let width = document.getElementById("s_bd_w").value;
  let qty = document.getElementById("s_bd_q").value;
  let sheet = document.getElementById("s_bd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/s_bd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".s_bd_cal").val(respData.square_feet);
        $(".s_bd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".s_bd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/s_bd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#third_bd").hide();
        $("#fourth_bd").hide();
        $("#fifth_bd").hide();
        $("#sixth_bd").hide();
        $("#seventh_bd").hide();
        $("#eightth_bd").hide();
        $("#ninth_bd").hide();
        if($(".t_bd_WBS").val() != "")
        {
          let height = document.getElementById("t_bd_h").value;
          let width = document.getElementById("t_bd_w").value;
          let qty = document.getElementById("t_bd_q").value;
          let sheet = document.getElementById("t_bd_sheet").value;
          let t_bd_cal = $(".t_bd_cal").val();
          let t_bd_WBS = $(".t_bd_WBS").val();

          $("#s_bd_h").val(height);
          $("#s_bd_w").val(width);
          $("#s_bd_q").val(qty);
          $("#s_bd_sheet").val(sheet);
          $(".s_bd_cal").val(t_bd_cal);
          $(".s_bd_WBS").val(t_bd_WBS);

          $("#t_bd_h").val("");
          $("#t_bd_w").val("");
          $("#t_bd_q").val("");
          $("#t_bd_sheet").val("");
          $(".t_bd_cal").val("");
          $(".t_bd_WBS").val("");               
        

        if($(".f_bd_WBS").val() != "")
        {
          let height = document.getElementById("f_bd_h").value;
          let width = document.getElementById("f_bd_w").value;
          let qty = document.getElementById("f_bd_q").value;
          let sheet = document.getElementById("f_bd_sheet").value;
          let f_bd_cal = $(".f_bd_cal").val();
          let f_bd_WBS = $(".f_bd_WBS").val();

          $("#t_bd_h").val(height);
          $("#t_bd_w").val(width);
          $("#t_bd_q").val(qty);
          $("#t_bd_sheet").val(sheet);
          $(".t_bd_cal").val(f_bd_cal);
          $(".t_bd_WBS").val(f_bd_WBS);

          $("#f_bd_h").val("");
          $("#f_bd_w").val("");
          $("#f_bd_q").val("");
          $("#f_bd_sheet").val("");
          $(".f_bd_cal").val("");
          $(".f_bd_WBS").val(""); 

          $("#third_bd").show();               
        }

        if($(".fth_bd_WBS").val() != "")
        {
          let height = document.getElementById("fth_bd_h").value;
          let width = document.getElementById("fth_bd_w").value;
          let qty = document.getElementById("fth_bd_q").value;
          let sheet = document.getElementById("fth_bd_sheet").value;
          let fth_bd_cal = $(".fth_bd_cal").val();
          let fth_bd_WBS = $(".fth_bd_WBS").val();

          $("#f_bd_h").val(height);
          $("#f_bd_w").val(width);
          $("#f_bd_q").val(qty);
          $("#f_bd_sheet").val(sheet);
          $(".f_bd_cal").val(fth_bd_cal);
          $(".f_bd_WBS").val(fth_bd_WBS);

          $("#fth_bd_h").val("");
          $("#fth_bd_w").val("");
          $("#fth_bd_q").val("");
          $("#fth_bd_sheet").val("");
          $(".fth_bd_cal").val("");
          $(".fth_bd_WBS").val("");     

          $("#fourth_bd").show();               
        }

        if($(".six_bd_WBS").val() != "")
        {
          let height = document.getElementById("six_bd_h").value;
          let width = document.getElementById("six_bd_w").value;
          let qty = document.getElementById("six_bd_q").value;
          let sheet = document.getElementById("six_bd_sheet").value;
          let six_bd_cal = $(".six_bd_cal").val();
          let six_bd_WBS = $(".six_bd_WBS").val();

          $("#fth_bd_h").val(height);
          $("#fth_bd_w").val(width);
          $("#fth_bd_q").val(qty);
          $("#fth_bd_sheet").val(sheet);
          $(".fth_bd_cal").val(six_bd_cal);
          $(".fth_bd_WBS").val(six_bd_WBS);

          $("#six_bd_h").val("");
          $("#six_bd_w").val("");
          $("#six_bd_q").val("");
          $("#six_bd_sheet").val("");
          $(".six_bd_cal").val("");
          $(".six_bd_WBS").val("");  

          $("#fifth_bd").show();              
        }

        if($(".sth_bd_WBS").val() != "")
        {
          let height = document.getElementById("sth_bd_h").value;
          let width = document.getElementById("sth_bd_w").value;
          let qty = document.getElementById("sth_bd_q").value;
          let sheet = document.getElementById("sth_bd_sheet").value;
          let sth_bd_cal = $(".sth_bd_cal").val();
          let sth_bd_WBS = $(".sth_bd_WBS").val();

          $("#six_bd_h").val(height);
          $("#six_bd_w").val(width);
          $("#six_bd_q").val(qty);
          $("#six_bd_sheet").val(sheet);
          $(".six_bd_cal").val(sth_bd_cal);
          $(".six_bd_WBS").val(sth_bd_WBS);

          $("#sth_bd_h").val("");
          $("#sth_bd_w").val("");
          $("#sth_bd_q").val("");
          $("#sth_bd_sheet").val("");
          $(".sth_bd_cal").val("");
          $(".sth_bd_WBS").val("");      

          $("#sixth_bd").show();                
        }

        if($(".e_bd_WBS").val() != "")
        {
          let height = document.getElementById("e_bd_h").value;
          let width = document.getElementById("e_bd_w").value;
          let qty = document.getElementById("e_bd_q").value;
          let sheet = document.getElementById("e_bd_sheet").value;
          let e_bd_cal = $(".e_bd_cal").val();
          let e_bd_WBS = $(".e_bd_WBS").val();

          $("#sth_bd_h").val(height);
          $("#sth_bd_w").val(width);
          $("#sth_bd_q").val(qty);
          $("#sth_bd_sheet").val(sheet);
          $(".sth_bd_cal").val(e_bd_cal);
          $(".sth_bd_WBS").val(e_bd_WBS);

          $("#e_bd_h").val("");
          $("#e_bd_w").val("");
          $("#e_bd_q").val("");
          $("#e_bd_sheet").val("");
          $(".e_bd_cal").val("");
          $(".e_bd_WBS").val("");      
$("#seventh_bd").show();                 
        }

        if($(".n_bd_WBS").val() != "")
        {
          let height = document.getElementById("n_bd_h").value;
          let width = document.getElementById("n_bd_w").value;
          let qty = document.getElementById("n_bd_q").value;
          let sheet = document.getElementById("n_bd_sheet").value;
          let n_bd_cal = $(".n_bd_cal").val();
          let n_bd_WBS = $(".n_bd_WBS").val();

          $("#e_bd_h").val(height);
          $("#e_bd_w").val(width);
          $("#e_bd_q").val(qty);
          $("#e_bd_sheet").val(sheet);
          $(".e_bd_cal").val(n_bd_cal);
          $(".e_bd_WBS").val(n_bd_WBS);

          $("#n_bd_h").val("");
          $("#n_bd_w").val("");
          $("#n_bd_q").val("");
          $("#n_bd_sheet").val("");
          $(".n_bd_cal").val("");
          $(".n_bd_WBS").val("");    
         $("#eightth_bd").show();               
        }
        }
        else{
         $("#s_bd_h").val("");
        $("#s_bd_w").val("");
        $("#s_bd_q").val("");
        $("#s_bd_sheet").val("");
        $(".s_bd_cal").val("");
        $(".s_bd_WBS").val("");
        }
       
      },
    });
});

$(document).on("click", "#t_bd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("t_bd_h").value;
  let width = document.getElementById("t_bd_w").value;
  let qty = document.getElementById("t_bd_q").value;
  let sheet = document.getElementById("t_bd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/t_bd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".t_bd_cal").val(respData.square_feet);
        $(".t_bd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".t_bd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/t_bd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#fourth_bd").hide();
        $("#fifth_bd").hide();
        $("#sixth_bd").hide();
        $("#seventh_bd").hide();
        $("#eightth_bd").hide();
        $("#ninth_bd").hide();
        if($(".f_bd_WBS").val() != "")
        {
          let height = document.getElementById("f_bd_h").value;
          let width = document.getElementById("f_bd_w").value;
          let qty = document.getElementById("f_bd_q").value;
          let sheet = document.getElementById("f_bd_sheet").value;
          let f_bd_cal = $(".f_bd_cal").val();
          let f_bd_WBS = $(".f_bd_WBS").val();

          $("#t_bd_h").val(height);
          $("#t_bd_w").val(width);
          $("#t_bd_q").val(qty);
          $("#t_bd_sheet").val(sheet);
          $(".t_bd_cal").val(f_bd_cal);
          $(".t_bd_WBS").val(f_bd_WBS);

          $("#f_bd_h").val("");
          $("#f_bd_w").val("");
          $("#f_bd_q").val("");
          $("#f_bd_sheet").val("");
          $(".f_bd_cal").val("");
          $(".f_bd_WBS").val("");               
        

        if($(".fth_bd_WBS").val() != "")
        {
          let height = document.getElementById("fth_bd_h").value;
          let width = document.getElementById("fth_bd_w").value;
          let qty = document.getElementById("fth_bd_q").value;
          let sheet = document.getElementById("fth_bd_sheet").value;
          let fth_bd_cal = $(".fth_bd_cal").val();
          let fth_bd_WBS = $(".fth_bd_WBS").val();

          $("#f_bd_h").val(height);
          $("#f_bd_w").val(width);
          $("#f_bd_q").val(qty);
          $("#f_bd_sheet").val(sheet);
          $(".f_bd_cal").val(fth_bd_cal);
          $(".f_bd_WBS").val(fth_bd_WBS);

          $("#fth_bd_h").val("");
          $("#fth_bd_w").val("");
          $("#fth_bd_q").val("");
          $("#fth_bd_sheet").val("");
          $(".fth_bd_cal").val("");
          $(".fth_bd_WBS").val("");     

          $("#fourth_bd").show();               
        }

        if($(".six_bd_WBS").val() != "")
        {
          let height = document.getElementById("six_bd_h").value;
          let width = document.getElementById("six_bd_w").value;
          let qty = document.getElementById("six_bd_q").value;
          let sheet = document.getElementById("six_bd_sheet").value;
          let six_bd_cal = $(".six_bd_cal").val();
          let six_bd_WBS = $(".six_bd_WBS").val();

          $("#fth_bd_h").val(height);
          $("#fth_bd_w").val(width);
          $("#fth_bd_q").val(qty);
          $("#fth_bd_sheet").val(sheet);
          $(".fth_bd_cal").val(six_bd_cal);
          $(".fth_bd_WBS").val(six_bd_WBS);

          $("#six_bd_h").val("");
          $("#six_bd_w").val("");
          $("#six_bd_q").val("");
          $("#six_bd_sheet").val("");
          $(".six_bd_cal").val("");
          $(".six_bd_WBS").val("");  

          $("#fifth_bd").show();              
        }

        if($(".sth_bd_WBS").val() != "")
        {
          let height = document.getElementById("sth_bd_h").value;
          let width = document.getElementById("sth_bd_w").value;
          let qty = document.getElementById("sth_bd_q").value;
          let sheet = document.getElementById("sth_bd_sheet").value;
          let sth_bd_cal = $(".sth_bd_cal").val();
          let sth_bd_WBS = $(".sth_bd_WBS").val();

          $("#six_bd_h").val(height);
          $("#six_bd_w").val(width);
          $("#six_bd_q").val(qty);
          $("#six_bd_sheet").val(sheet);
          $(".six_bd_cal").val(sth_bd_cal);
          $(".six_bd_WBS").val(sth_bd_WBS);

          $("#sth_bd_h").val("");
          $("#sth_bd_w").val("");
          $("#sth_bd_q").val("");
          $("#sth_bd_sheet").val("");
          $(".sth_bd_cal").val("");
          $(".sth_bd_WBS").val("");      

          $("#sixth_bd").show();                
        }

        if($(".e_bd_WBS").val() != "")
        {
          let height = document.getElementById("e_bd_h").value;
          let width = document.getElementById("e_bd_w").value;
          let qty = document.getElementById("e_bd_q").value;
          let sheet = document.getElementById("e_bd_sheet").value;
          let e_bd_cal = $(".e_bd_cal").val();
          let e_bd_WBS = $(".e_bd_WBS").val();

          $("#sth_bd_h").val(height);
          $("#sth_bd_w").val(width);
          $("#sth_bd_q").val(qty);
          $("#sth_bd_sheet").val(sheet);
          $(".sth_bd_cal").val(e_bd_cal);
          $(".sth_bd_WBS").val(e_bd_WBS);

          $("#e_bd_h").val("");
          $("#e_bd_w").val("");
          $("#e_bd_q").val("");
          $("#e_bd_sheet").val("");
          $(".e_bd_cal").val("");
          $(".e_bd_WBS").val("");      
$("#seventh_bd").show();                 
        }

        if($(".n_bd_WBS").val() != "")
        {
          let height = document.getElementById("n_bd_h").value;
          let width = document.getElementById("n_bd_w").value;
          let qty = document.getElementById("n_bd_q").value;
          let sheet = document.getElementById("n_bd_sheet").value;
          let n_bd_cal = $(".n_bd_cal").val();
          let n_bd_WBS = $(".n_bd_WBS").val();

          $("#e_bd_h").val(height);
          $("#e_bd_w").val(width);
          $("#e_bd_q").val(qty);
          $("#e_bd_sheet").val(sheet);
          $(".e_bd_cal").val(n_bd_cal);
          $(".e_bd_WBS").val(n_bd_WBS);

          $("#n_bd_h").val("");
          $("#n_bd_w").val("");
          $("#n_bd_q").val("");
          $("#n_bd_sheet").val("");
          $(".n_bd_cal").val("");
          $(".n_bd_WBS").val("");    
$("#eight_bd").show();                
        }
        }
        else{
         $("#t_bd_h").val("");
        $("#t_bd_w").val("");
        $("#t_bd_q").val("");
        $("#t_bd_sheet").val("");
        $(".t_bd_cal").val("");
        $(".t_bd_WBS").val("");
        }
        
      },
    });
});

$(document).on("click", "#f_bd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("f_bd_h").value;
  let width = document.getElementById("f_bd_w").value;
  let qty = document.getElementById("f_bd_q").value;
  let sheet = document.getElementById("f_bd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/f_bd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".f_bd_cal").val(respData.square_feet);
        $(".f_bd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".f_bd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/f_bd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#fifth_bd").hide();
        $("#sixth_bd").hide();
        $("#seventh_bd").hide();
        $("#eightth_bd").hide();
        $("#ninth_bd").hide();
        if($(".fth_bd_WBS").val() != "")
        {
          let height = document.getElementById("fth_bd_h").value;
          let width = document.getElementById("fth_bd_w").value;
          let qty = document.getElementById("fth_bd_q").value;
          let sheet = document.getElementById("fth_bd_sheet").value;
          let fth_bd_cal = $(".fth_bd_cal").val();
          let fth_bd_WBS = $(".fth_bd_WBS").val();

          $("#f_bd_h").val(height);
          $("#f_bd_w").val(width);
          $("#f_bd_q").val(qty);
          $("#f_bd_sheet").val(sheet);
          $(".f_bd_cal").val(fth_bd_cal);
          $(".f_bd_WBS").val(fth_bd_WBS);

          $("#fth_bd_h").val("");
          $("#fth_bd_w").val("");
          $("#fth_bd_q").val("");
          $("#fth_bd_sheet").val("");
          $(".fth_bd_cal").val("");
          $(".fth_bd_WBS").val("");               
        

        if($(".six_bd_WBS").val() != "")
        {
          let height = document.getElementById("six_bd_h").value;
          let width = document.getElementById("six_bd_w").value;
          let qty = document.getElementById("six_bd_q").value;
          let sheet = document.getElementById("six_bd_sheet").value;
          let six_bd_cal = $(".six_bd_cal").val();
          let six_bd_WBS = $(".six_bd_WBS").val();

          $("#fth_bd_h").val(height);
          $("#fth_bd_w").val(width);
          $("#fth_bd_q").val(qty);
          $("#fth_bd_sheet").val(sheet);
          $(".fth_bd_cal").val(six_bd_cal);
          $(".fth_bd_WBS").val(six_bd_WBS);

          $("#six_bd_h").val("");
          $("#six_bd_w").val("");
          $("#six_bd_q").val("");
          $("#six_bd_sheet").val("");
          $(".six_bd_cal").val("");
          $(".six_bd_WBS").val("");  

          $("#fifth_bd").show();              
        }

        if($(".sth_bd_WBS").val() != "")
        {
          let height = document.getElementById("sth_bd_h").value;
          let width = document.getElementById("sth_bd_w").value;
          let qty = document.getElementById("sth_bd_q").value;
          let sheet = document.getElementById("sth_bd_sheet").value;
          let sth_bd_cal = $(".sth_bd_cal").val();
          let sth_bd_WBS = $(".sth_bd_WBS").val();

          $("#six_bd_h").val(height);
          $("#six_bd_w").val(width);
          $("#six_bd_q").val(qty);
          $("#six_bd_sheet").val(sheet);
          $(".six_bd_cal").val(sth_bd_cal);
          $(".six_bd_WBS").val(sth_bd_WBS);

          $("#sth_bd_h").val("");
          $("#sth_bd_w").val("");
          $("#sth_bd_q").val("");
          $("#sth_bd_sheet").val("");
          $(".sth_bd_cal").val("");
          $(".sth_bd_WBS").val("");      

          $("#sixth_bd").show();                
        }

        if($(".e_bd_WBS").val() != "")
        {
          let height = document.getElementById("e_bd_h").value;
          let width = document.getElementById("e_bd_w").value;
          let qty = document.getElementById("e_bd_q").value;
          let sheet = document.getElementById("e_bd_sheet").value;
          let e_bd_cal = $(".e_bd_cal").val();
          let e_bd_WBS = $(".e_bd_WBS").val();

          $("#sth_bd_h").val(height);
          $("#sth_bd_w").val(width);
          $("#sth_bd_q").val(qty);
          $("#sth_bd_sheet").val(sheet);
          $(".sth_bd_cal").val(e_bd_cal);
          $(".sth_bd_WBS").val(e_bd_WBS);

          $("#e_bd_h").val("");
          $("#e_bd_w").val("");
          $("#e_bd_q").val("");
          $("#e_bd_sheet").val("");
          $(".e_bd_cal").val("");
          $(".e_bd_WBS").val("");      
$("#seventh_bd").show();                 
        }

        if($(".n_bd_WBS").val() != "")
        {
          let height = document.getElementById("n_bd_h").value;
          let width = document.getElementById("n_bd_w").value;
          let qty = document.getElementById("n_bd_q").value;
          let sheet = document.getElementById("n_bd_sheet").value;
          let n_bd_cal = $(".n_bd_cal").val();
          let n_bd_WBS = $(".n_bd_WBS").val();

          $("#e_bd_h").val(height);
          $("#e_bd_w").val(width);
          $("#e_bd_q").val(qty);
          $("#e_bd_sheet").val(sheet);
          $(".e_bd_cal").val(n_bd_cal);
          $(".e_bd_WBS").val(n_bd_WBS);

          $("#n_bd_h").val("");
          $("#n_bd_w").val("");
          $("#n_bd_q").val("");
          $("#n_bd_sheet").val("");
          $(".n_bd_cal").val("");
          $(".n_bd_WBS").val("");    
$("#eight_bd").show();                
        }
        }
        else{
          $("#f_bd_h").val("");
          $("#f_bd_w").val("");
          $("#f_bd_q").val("");
          $("#f_bd_sheet").val("");
          $(".f_bd_cal").val("");
          $(".f_bd_WBS").val("");
        }
        
      },
    });
});

$(document).on("click", "#fth_bd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("fth_bd_h").value;
  let width = document.getElementById("fth_bd_w").value;
  let qty = document.getElementById("fth_bd_q").value;
  let sheet = document.getElementById("fth_bd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/fth_bd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".fth_bd_cal").val(respData.square_feet);
        $(".fth_bd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".fth_bd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/fth_bd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#sixth_bd").hide();
        $("#seventh_bd").hide();
        $("#eightth_bd").hide();
        $("#ninth_bd").hide();
        if($(".six_bd_WBS").val() != "")
        {
          let height = document.getElementById("six_bd_h").value;
          let width = document.getElementById("six_bd_w").value;
          let qty = document.getElementById("six_bd_q").value;
          let sheet = document.getElementById("six_bd_sheet").value;
          let six_bd_cal = $(".six_bd_cal").val();
          let six_bd_WBS = $(".six_bd_WBS").val();

          $("#fth_bd_h").val(height);
          $("#fth_bd_w").val(width);
          $("#fth_bd_q").val(qty);
          $("#fth_bd_sheet").val(sheet);
          $(".fth_bd_cal").val(six_bd_cal);
          $(".fth_bd_WBS").val(six_bd_WBS);

          $("#six_bd_h").val("");
          $("#six_bd_w").val("");
          $("#six_bd_q").val("");
          $("#six_bd_sheet").val("");
          $(".six_bd_cal").val("");
          $(".six_bd_WBS").val("");               
        

        if($(".sth_bd_WBS").val() != "")
        {
          let height = document.getElementById("sth_bd_h").value;
          let width = document.getElementById("sth_bd_w").value;
          let qty = document.getElementById("sth_bd_q").value;
          let sheet = document.getElementById("sth_bd_sheet").value;
          let sth_bd_cal = $(".sth_bd_cal").val();
          let sth_bd_WBS = $(".sth_bd_WBS").val();

          $("#six_bd_h").val(height);
          $("#six_bd_w").val(width);
          $("#six_bd_q").val(qty);
          $("#six_bd_sheet").val(sheet);
          $(".six_bd_cal").val(sth_bd_cal);
          $(".six_bd_WBS").val(sth_bd_WBS);

          $("#sth_bd_h").val("");
          $("#sth_bd_w").val("");
          $("#sth_bd_q").val("");
          $("#sth_bd_sheet").val("");
          $(".sth_bd_cal").val("");
          $(".sth_bd_WBS").val("");      

          $("#sixth_bd").show();                
        }

        if($(".e_bd_WBS").val() != "")
        {
          let height = document.getElementById("e_bd_h").value;
          let width = document.getElementById("e_bd_w").value;
          let qty = document.getElementById("e_bd_q").value;
          let sheet = document.getElementById("e_bd_sheet").value;
          let e_bd_cal = $(".e_bd_cal").val();
          let e_bd_WBS = $(".e_bd_WBS").val();

          $("#sth_bd_h").val(height);
          $("#sth_bd_w").val(width);
          $("#sth_bd_q").val(qty);
          $("#sth_bd_sheet").val(sheet);
          $(".sth_bd_cal").val(e_bd_cal);
          $(".sth_bd_WBS").val(e_bd_WBS);

          $("#e_bd_h").val("");
          $("#e_bd_w").val("");
          $("#e_bd_q").val("");
          $("#e_bd_sheet").val("");
          $(".e_bd_cal").val("");
          $(".e_bd_WBS").val("");      
$("#seventh_bd").show();                 
        }

        if($(".n_bd_WBS").val() != "")
        {
          let height = document.getElementById("n_bd_h").value;
          let width = document.getElementById("n_bd_w").value;
          let qty = document.getElementById("n_bd_q").value;
          let sheet = document.getElementById("n_bd_sheet").value;
          let n_bd_cal = $(".n_bd_cal").val();
          let n_bd_WBS = $(".n_bd_WBS").val();

          $("#e_bd_h").val(height);
          $("#e_bd_w").val(width);
          $("#e_bd_q").val(qty);
          $("#e_bd_sheet").val(sheet);
          $(".e_bd_cal").val(n_bd_cal);
          $(".e_bd_WBS").val(n_bd_WBS);

          $("#n_bd_h").val("");
          $("#n_bd_w").val("");
          $("#n_bd_q").val("");
          $("#n_bd_sheet").val("");
          $(".n_bd_cal").val("");
          $(".n_bd_WBS").val("");    
$("#eight_bd").show();                
        }
        }
        else{
          $("#fth_bd_h").val("");
        $("#fth_bd_w").val("");
        $("#fth_bd_q").val("");
        $("#fth_bd_sheet").val("");
        $(".fth_bd_cal").val("");
        $(".fth_bd_WBS").val("");
        }
        
      },
    });
});

$(document).on("click", "#six_bd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("six_bd_h").value;
  let width = document.getElementById("six_bd_w").value;
  let qty = document.getElementById("six_bd_q").value;
  let sheet = document.getElementById("six_bd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/six_bd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".six_bd_cal").val(respData.square_feet);
        $(".six_bd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".six_bd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/six_bd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#seventh_bd").hide();
        $("#eightth_bd").hide();
        $("#ninth_bd").hide();
        if($(".sth_bd_WBS").val() != "")
        {
          let height = document.getElementById("sth_bd_h").value;
          let width = document.getElementById("sth_bd_w").value;
          let qty = document.getElementById("sth_bd_q").value;
          let sheet = document.getElementById("sth_bd_sheet").value;
          let sth_bd_cal = $(".sth_bd_cal").val();
          let sth_bd_WBS = $(".sth_bd_WBS").val();

          $("#six_bd_h").val(height);
          $("#six_bd_w").val(width);
          $("#six_bd_q").val(qty);
          $("#six_bd_sheet").val(sheet);
          $(".six_bd_cal").val(sth_bd_cal);
          $(".six_bd_WBS").val(sth_bd_WBS);

          $("#sth_bd_h").val("");
          $("#sth_bd_w").val("");
          $("#sth_bd_q").val("");
          $("#sth_bd_sheet").val("");
          $(".sth_bd_cal").val("");
          $(".sth_bd_WBS").val("");      

          $("#sixth_bd").show();                
        

        if($(".e_bd_WBS").val() != "")
        {
          let height = document.getElementById("e_bd_h").value;
          let width = document.getElementById("e_bd_w").value;
          let qty = document.getElementById("e_bd_q").value;
          let sheet = document.getElementById("e_bd_sheet").value;
          let e_bd_cal = $(".e_bd_cal").val();
          let e_bd_WBS = $(".e_bd_WBS").val();

          $("#sth_bd_h").val(height);
          $("#sth_bd_w").val(width);
          $("#sth_bd_q").val(qty);
          $("#sth_bd_sheet").val(sheet);
          $(".sth_bd_cal").val(e_bd_cal);
          $(".sth_bd_WBS").val(e_bd_WBS);

          $("#e_bd_h").val("");
          $("#e_bd_w").val("");
          $("#e_bd_q").val("");
          $("#e_bd_sheet").val("");
          $(".e_bd_cal").val("");
          $(".e_bd_WBS").val("");      
$("#seventh_bd").show();                 
        }

        if($(".n_bd_WBS").val() != "")
        {
          let height = document.getElementById("n_bd_h").value;
          let width = document.getElementById("n_bd_w").value;
          let qty = document.getElementById("n_bd_q").value;
          let sheet = document.getElementById("n_bd_sheet").value;
          let n_bd_cal = $(".n_bd_cal").val();
          let n_bd_WBS = $(".n_bd_WBS").val();

          $("#e_bd_h").val(height);
          $("#e_bd_w").val(width);
          $("#e_bd_q").val(qty);
          $("#e_bd_sheet").val(sheet);
          $(".e_bd_cal").val(n_bd_cal);
          $(".e_bd_WBS").val(n_bd_WBS);

          $("#n_bd_h").val("");
          $("#n_bd_w").val("");
          $("#n_bd_q").val("");
          $("#n_bd_sheet").val("");
          $(".n_bd_cal").val("");
          $(".n_bd_WBS").val("");    
$("#eight_bd").show();                
        }
        }
        else{
        $("#six_bd_h").val("");
        $("#six_bd_w").val("");
        $("#six_bd_q").val("");
        $("#six_bd_sheet").val("");
        $(".six_bd_cal").val("");
        $(".six_bd_WBS").val("");
        }
        
      },
    });
});

$(document).on("click", "#sth_bd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("sth_bd_h").value;
  let width = document.getElementById("sth_bd_w").value;
  let qty = document.getElementById("sth_bd_q").value;
  let sheet = document.getElementById("sth_bd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/sth_bd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".sth_bd_cal").val(respData.square_feet);
        $(".sth_bd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".sth_bd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/sth_bd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#eightth_bd").hide();
        $("#ninth_bd").hide();
        if($(".e_bd_WBS").val() != "")
        {
          let height = document.getElementById("e_bd_h").value;
          let width = document.getElementById("e_bd_w").value;
          let qty = document.getElementById("e_bd_q").value;
          let sheet = document.getElementById("e_bd_sheet").value;
          let e_bd_cal = $(".e_bd_cal").val();
          let e_bd_WBS = $(".e_bd_WBS").val();

          $("#sth_bd_h").val(height);
          $("#sth_bd_w").val(width);
          $("#sth_bd_q").val(qty);
          $("#sth_bd_sheet").val(sheet);
          $(".sth_bd_cal").val(e_bd_cal);
          $(".sth_bd_WBS").val(e_bd_WBS);

          $("#e_bd_h").val("");
          $("#e_bd_w").val("");
          $("#e_bd_q").val("");
          $("#e_bd_sheet").val("");
          $(".e_bd_cal").val("");
          $(".e_bd_WBS").val("");               
        

        if($(".n_bd_WBS").val() != "")
        {
          let height = document.getElementById("n_bd_h").value;
          let width = document.getElementById("n_bd_w").value;
          let qty = document.getElementById("n_bd_q").value;
          let sheet = document.getElementById("n_bd_sheet").value;
          let n_bd_cal = $(".n_bd_cal").val();
          let n_bd_WBS = $(".n_bd_WBS").val();

          $("#e_bd_h").val(height);
          $("#e_bd_w").val(width);
          $("#e_bd_q").val(qty);
          $("#e_bd_sheet").val(sheet);
          $(".e_bd_cal").val(n_bd_cal);
          $(".e_bd_WBS").val(n_bd_WBS);

          $("#n_bd_h").val("");
          $("#n_bd_w").val("");
          $("#n_bd_q").val("");
          $("#n_bd_sheet").val("");
          $(".n_bd_cal").val("");
          $(".n_bd_WBS").val("");    
         $("#eightth_bd").show();               
        }
        }
        else{
          $("#sth_bd_h").val("");
          $("#sth_bd_w").val("");
          $("#sth_bd_q").val("");
          $("#sth_bd_sheet").val("");
          $(".sth_bd_cal").val("");
          $(".sth_bd_WBS").val("");
        }
       
      },
    });
});

$(document).on("click", "#e_bd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("e_bd_h").value;
  let width = document.getElementById("e_bd_w").value;
  let qty = document.getElementById("e_bd_q").value;
  let sheet = document.getElementById("e_bd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/e_bd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".e_bd_cal").val(respData.square_feet);
        $(".e_bd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".e_bd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/e_bd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#ninth_bd").hide();
        if($(".n_bd_WBS").val() != "")
        {
          let height = document.getElementById("n_bd_h").value;
          let width = document.getElementById("n_bd_w").value;
          let qty = document.getElementById("n_bd_q").value;
          let sheet = document.getElementById("n_bd_sheet").value;
          let n_bd_cal = $(".n_bd_cal").val();
          let n_bd_WBS = $(".n_bd_WBS").val();

          $("#e_bd_h").val(height);
          $("#e_bd_w").val(width);
          $("#e_bd_q").val(qty);
          $("#e_bd_sheet").val(sheet);
          $(".e_bd_cal").val(n_bd_cal);
          $(".e_bd_WBS").val(n_bd_WBS);

          $("#n_bd_h").val("");
          $("#n_bd_w").val("");
          $("#n_bd_q").val("");
          $("#n_bd_sheet").val("");
          $(".n_bd_cal").val("");
          $(".n_bd_WBS").val("");    
$("#eight_bd").show();                
        }
        
        else{
          $("#e_bd_h").val("");
        $("#e_bd_w").val("");
        $("#e_bd_q").val("");
        $("#e_bd_sheet").val("");
        $(".e_bd_cal").val("");
        $(".e_bd_WBS").val("");
        }
        
      },
    });
});

$(document).on("click", "#n_bd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("n_bd_h").value;
  let width = document.getElementById("n_bd_w").value;
  let qty = document.getElementById("n_bd_q").value;
  let sheet = document.getElementById("n_bd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/n_bd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".n_bd_cal").val(respData.square_feet);
        $(".n_bd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".n_bd_del", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/n_bd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data) {
        $("#n_bd_h").val("");
        $("#n_bd_w").val("");
        $("#n_bd_q").val("");
        $("#n_bd_sheet").val("");
        $(".n_bd_cal").val("");
        $(".n_bd_WBS").val("");
      },
    });
});


// For calcuation of side door
$(document).on("click", "#sd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("sd_h").value;
  let width = document.getElementById("sd_w").value;
  let qty = document.getElementById("sd_q").value;
  let sheet = document.getElementById("sd_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/sd.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".sd_cal").val(respData.square_feet);
        $(".sd_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});

$(document).on("click", ".sd_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/sd_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
       $("#sd_h").val("");
       $("#sd_w").val("");
       $("#sd_q").val("");
      $("#sd_sheet").val("");
      $(".sd_WBS").val("");
      $(".sd_cal").val("");
      },
    });
    $("#four").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of vertocal structure
$(document).on("click", "#vs_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("vs_h").value;
  let width = document.getElementById("vs_w").value;
  let qty = document.getElementById("vs_q").value;
  let sheet = document.getElementById("vs_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/vs.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".vs_cal").val(respData.square_feet);
        $(".vs_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".vs_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/vs_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        alert(data)
        $("#vs_h").val("");
        $("#vs_w").val("");
        $("#vs_q").val("");
        $("#vs_sheet").val("");
        $(".vs_WBS").val("");
        $(".vs_cal").val("");
      },
    });
    $("#five").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of horizontal struvture
$(document).on("click", "#hs_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("hs_h").value;
  let width = document.getElementById("hs_w").value;
  let qty = document.getElementById("hs_q").value;
  let sheet = document.getElementById("hs_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/hs.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".hs_cal").val(respData.square_feet);
        $(".hs_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".hs_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/hs_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#hs_h").val("");
        $("#hs_w").val("");
        $("#hs_q").val("");
        $("#hs_sheet").val("");
        $(".hs_WBS").val("");
        $(".hs_cal").val("");
      },
    });
    $("#six").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of horizontal struvture
$(document).on("click", "#hsp_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("hsp_h").value;
  let width = document.getElementById("hsp_w").value;
  let qty = document.getElementById("hsp_q").value;
  let sheet = document.getElementById("hsp_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/hsp.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".hsp_cal").val(respData.square_feet);
        $(".hsp_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".hsp_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/hsp_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#hsp_h").val("");
        $("#hsp_w").val("");
        $("#hsp_q").val("");
        $("#hsp_sheet").val("");
        $(".hsp_WBS").val("");
        $(".hsp_cal").val("");
      },
    });
    $("#seven").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of component plate
$(document).on("click", "#cp_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("cp_h").value;
  let width = document.getElementById("cp_w").value;
  let qty = document.getElementById("cp_q").value;
  let sheet = document.getElementById("cp_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/cp.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".cp_cal").val(respData.square_feet);
        $(".cp_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".cp_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/cp_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
        $("#second_cp").hide();
        $("#third_cp").hide();
        $("#fourth_cp").hide();
        $("#fifth_cp").hide();
        $("#sixth_cp").hide();
        $("#seventh_cp").hide();
        $("#eighth_cp").hide();
        $("#ninth_cp").hide();
        if($(".s_cp_WBS").val() != "")
        {
          let height = document.getElementById("s_cp_h").value;
          let width = document.getElementById("s_cp_w").value;
          let qty = document.getElementById("s_cp_q").value;
          let sheet = document.getElementById("s_cp_sheet").value;
          let s_cp_cal = $(".s_cp_cal").val();
          let s_cp_WBS = $(".s_cp_WBS").val();

          $("#cp_h").val(height);
          $("#cp_w").val(width);
          $("#cp_q").val(qty);
          $("#cp_sheet").val(sheet);
          $(".cp_cal").val(s_cp_cal);
          $(".cp_WBS").val(s_cp_WBS);

          $("#s_cp_h").val("");
          $("#s_cp_w").val("");
          $("#s_cp_q").val("");
          $("#s_cp_sheet").val("");
          $(".s_cp_cal").val("");
          $(".s_cp_WBS").val("");               
        

        if($(".t_cp_WBS").val() != "")
        {
          let height = document.getElementById("t_cp_h").value;
          let width = document.getElementById("t_cp_w").value;
          let qty = document.getElementById("t_cp_q").value;
          let sheet = document.getElementById("t_cp_sheet").value;
          let t_cp_cal = $(".t_cp_cal").val();
          let t_cp_WBS = $(".t_cp_WBS").val();

          $("#s_cp_h").val(height);
          $("#s_cp_w").val(width);
          $("#s_cp_q").val(qty);
          $("#s_cp_sheet").val(sheet);
          $(".s_cp_cal").val(t_cp_cal);
          $(".s_cp_WBS").val(t_cp_WBS);

          $("#t_cp_h").val("");
          $("#t_cp_w").val("");
          $("#t_cp_q").val("");
          $("#t_cp_sheet").val("");
          $(".t_cp_cal").val("");
          $(".t_cp_WBS").val("");      
          $("#second_cp").show();         
        }

        if($(".f_cp_WBS").val() != "")
        {
          let height = document.getElementById("f_cp_h").value;
          let width = document.getElementById("f_cp_w").value;
          let qty = document.getElementById("f_cp_q").value;
          let sheet = document.getElementById("f_cp_sheet").value;
          let f_cp_cal = $(".f_cp_cal").val();
          let f_cp_WBS = $(".f_cp_WBS").val();

          $("#t_cp_h").val(height);
          $("#t_cp_w").val(width);
          $("#t_cp_q").val(qty);
          $("#t_cp_sheet").val(sheet);
          $(".t_cp_cal").val(f_cp_cal);
          $(".t_cp_WBS").val(f_cp_WBS);

          $("#f_cp_h").val("");
          $("#f_cp_w").val("");
          $("#f_cp_q").val("");
          $("#f_cp_sheet").val("");
          $(".f_cp_cal").val("");
          $(".f_cp_WBS").val("");  
          $("#third_cp").show();             
        }

        if($(".fth_cp_WBS").val() != "")
        {
          let height = document.getElementById("fth_cp_h").value;
          let width = document.getElementById("fth_cp_w").value;
          let qty = document.getElementById("fth_cp_q").value;
          let sheet = document.getElementById("fth_cp_sheet").value;
          let fth_cp_cal = $(".fth_cp_cal").val();
          let fth_cp_WBS = $(".fth_cp_WBS").val();

          $("#f_cp_h").val(height);
          $("#f_cp_w").val(width);
          $("#f_cp_q").val(qty);
          $("#f_cp_sheet").val(sheet);
          $(".f_cp_cal").val(fth_cp_cal);
          $(".f_cp_WBS").val(fth_cp_WBS);

          $("#fth_cp_h").val("");
          $("#fth_cp_w").val("");
          $("#fth_cp_q").val("");
          $("#fth_cp_sheet").val("");
          $(".fth_cp_cal").val("");
          $(".fth_cp_WBS").val("");
          $("#fourth_cp").show();               
        }

        if($(".six_cp_WBS").val() != "")
        {
          let height = document.getElementById("six_cp_h").value;
          let width = document.getElementById("six_cp_w").value;
          let qty = document.getElementById("six_cp_q").value;
          let sheet = document.getElementById("six_cp_sheet").value;
          let six_cp_cal = $(".six_cp_cal").val();
          let six_cp_WBS = $(".six_cp_WBS").val();

          $("#fth_cp_h").val(height);
          $("#fth_cp_w").val(width);
          $("#fth_cp_q").val(qty);
          $("#fth_cp_sheet").val(sheet);
          $(".fth_cp_cal").val(six_cp_cal);
          $(".fth_cp_WBS").val(six_cp_WBS);

          $("#six_cp_h").val("");
          $("#six_cp_w").val("");
          $("#six_cp_q").val("");
          $("#six_cp_sheet").val("");
          $(".six_cp_cal").val("");
          $(".six_cp_WBS").val(""); 
          $("#fifth_cp").show();              
        }

        if($(".sth_cp_WBS").val() != "")
        {
          let height = document.getElementById("sth_cp_h").value;
          let width = document.getElementById("sth_cp_w").value;
          let qty = document.getElementById("sth_cp_q").value;
          let sheet = document.getElementById("sth_cp_sheet").value;
          let sth_cp_cal = $(".sth_cp_cal").val();
          let sth_cp_WBS = $(".sth_cp_WBS").val();

          $("#six_cp_h").val(height);
          $("#six_cp_w").val(width);
          $("#six_cp_q").val(qty);
          $("#six_cp_sheet").val(sheet);
          $(".six_cp_cal").val(sth_cp_cal);
          $(".six_cp_WBS").val(sth_cp_WBS);

          $("#sth_cp_h").val("");
          $("#sth_cp_w").val("");
          $("#sth_cp_q").val("");
          $("#sth_cp_sheet").val("");
          $(".sth_cp_cal").val("");
          $(".sth_cp_WBS").val(""); 
          $("#sixth_cp").show();              
        }

        if($(".e_cp_WBS").val() != "")
        {
          let height = document.getElementById("e_cp_h").value;
          let width = document.getElementById("e_cp_w").value;
          let qty = document.getElementById("e_cp_q").value;
          let sheet = document.getElementById("e_cp_sheet").value;
          let e_cp_cal = $(".e_cp_cal").val();
          let e_cp_WBS = $(".e_cp_WBS").val();

          $("#sth_cp_h").val(height);
          $("#sth_cp_w").val(width);
          $("#sth_cp_q").val(qty);
          $("#sth_cp_sheet").val(sheet);
          $(".sth_cp_cal").val(e_cp_cal);
          $(".sth_cp_WBS").val(e_cp_WBS);

          $("#e_cp_h").val("");
          $("#e_cp_w").val("");
          $("#e_cp_q").val("");
          $("#e_cp_sheet").val("");
          $(".e_cp_cal").val("");
          $(".e_cp_WBS").val("");
          $("#seventh_cp").show();               
        }

        if($(".n_cp_WBS").val() != "")
        {
          let height = document.getElementById("n_cp_h").value;
          let width = document.getElementById("n_cp_w").value;
          let qty = document.getElementById("n_cp_q").value;
          let sheet = document.getElementById("n_cp_sheet").value;
          let n_cp_cal = $(".n_cp_cal").val();
          let n_cp_WBS = $(".n_cp_WBS").val();

          $("#e_cp_h").val(height);
          $("#e_cp_w").val(width);
          $("#e_cp_q").val(qty);
          $("#e_cp_sheet").val(sheet);
          $(".e_cp_cal").val(n_cp_cal);
          $(".e_cp_WBS").val(n_cp_WBS);

          $("#n_cp_h").val("");
          $("#n_cp_w").val("");
          $("#n_cp_q").val("");
          $("#n_cp_sheet").val("");
          $(".n_cp_cal").val("");
          $(".n_cp_WBS").val(""); 
          $("#eighth_cp").show();              
        }
        }
        else{
        $("#cp_h").val("");
        $("#cp_w").val("");
        $("#cp_q").val("");
        $("#cp_sheet").val("");
        $(".cp_cal").val("");
        $(".cp_WBS").val("");
        }
      },
    });
}); 

$(document).on("click", "#s_cp_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("s_cp_h").value;
  let width = document.getElementById("s_cp_w").value;
  let qty = document.getElementById("s_cp_q").value;
  let sheet = document.getElementById("s_cp_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/s_cp.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".s_cp_cal").val(respData.square_feet);
        $(".s_cp_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".s_cp_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/s_cp_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
        $("#third_cp").hide();
        $("#fourth_cp").hide();
        $("#fifth_cp").hide();
        $("#sixth_cp").hide();
        $("#seventh_cp").hide();
        $("#eighth_cp").hide();
        $("#ninth_cp").hide();
        if($(".t_cp_WBS").val() != "")
        {
          let height = document.getElementById("t_cp_h").value;
          let width = document.getElementById("t_cp_w").value;
          let qty = document.getElementById("t_cp_q").value;
          let sheet = document.getElementById("t_cp_sheet").value;
          let t_cp_cal = $(".t_cp_cal").val();
          let t_cp_WBS = $(".t_cp_WBS").val();

          $("#s_cp_h").val(height);
          $("#s_cp_w").val(width);
          $("#s_cp_q").val(qty);
          $("#s_cp_sheet").val(sheet);
          $(".s_cp_cal").val(t_cp_cal);
          $(".s_cp_WBS").val(t_cp_WBS);

          $("#t_cp_h").val("");
          $("#t_cp_w").val("");
          $("#t_cp_q").val("");
          $("#t_cp_sheet").val("");
          $(".t_cp_cal").val("");
          $(".t_cp_WBS").val("");      
          $("#second_cp").show();              
        

        if($(".f_cp_WBS").val() != "")
        {
          let height = document.getElementById("f_cp_h").value;
          let width = document.getElementById("f_cp_w").value;
          let qty = document.getElementById("f_cp_q").value;
          let sheet = document.getElementById("f_cp_sheet").value;
          let f_cp_cal = $(".f_cp_cal").val();
          let f_cp_WBS = $(".f_cp_WBS").val();

          $("#t_cp_h").val(height);
          $("#t_cp_w").val(width);
          $("#t_cp_q").val(qty);
          $("#t_cp_sheet").val(sheet);
          $(".t_cp_cal").val(f_cp_cal);
          $(".t_cp_WBS").val(f_cp_WBS);

          $("#f_cp_h").val("");
          $("#f_cp_w").val("");
          $("#f_cp_q").val("");
          $("#f_cp_sheet").val("");
          $(".f_cp_cal").val("");
          $(".f_cp_WBS").val("");  
          $("#third_cp").show();                
        }

        if($(".fth_cp_WBS").val() != "")
        {
          let height = document.getElementById("fth_cp_h").value;
          let width = document.getElementById("fth_cp_w").value;
          let qty = document.getElementById("fth_cp_q").value;
          let sheet = document.getElementById("fth_cp_sheet").value;
          let fth_cp_cal = $(".fth_cp_cal").val();
          let fth_cp_WBS = $(".fth_cp_WBS").val();

          $("#f_cp_h").val(height);
          $("#f_cp_w").val(width);
          $("#f_cp_q").val(qty);
          $("#f_cp_sheet").val(sheet);
          $(".f_cp_cal").val(fth_cp_cal);
          $(".f_cp_WBS").val(fth_cp_WBS);

          $("#fth_cp_h").val("");
          $("#fth_cp_w").val("");
          $("#fth_cp_q").val("");
          $("#fth_cp_sheet").val("");
          $(".fth_cp_cal").val("");
          $(".fth_cp_WBS").val("");
          $("#fourth_cp").show();               
        }

        if($(".six_cp_WBS").val() != "")
        {
          let height = document.getElementById("six_cp_h").value;
          let width = document.getElementById("six_cp_w").value;
          let qty = document.getElementById("six_cp_q").value;
          let sheet = document.getElementById("six_cp_sheet").value;
          let six_cp_cal = $(".six_cp_cal").val();
          let six_cp_WBS = $(".six_cp_WBS").val();

          $("#fth_cp_h").val(height);
          $("#fth_cp_w").val(width);
          $("#fth_cp_q").val(qty);
          $("#fth_cp_sheet").val(sheet);
          $(".fth_cp_cal").val(six_cp_cal);
          $(".fth_cp_WBS").val(six_cp_WBS);

          $("#six_cp_h").val("");
          $("#six_cp_w").val("");
          $("#six_cp_q").val("");
          $("#six_cp_sheet").val("");
          $(".six_cp_cal").val("");
          $(".six_cp_WBS").val(""); 
          $("#fifth_cp").show();                
        }

        if($(".sth_cp_WBS").val() != "")
        {
          let height = document.getElementById("sth_cp_h").value;
          let width = document.getElementById("sth_cp_w").value;
          let qty = document.getElementById("sth_cp_q").value;
          let sheet = document.getElementById("sth_cp_sheet").value;
          let sth_cp_cal = $(".sth_cp_cal").val();
          let sth_cp_WBS = $(".sth_cp_WBS").val();

          $("#six_cp_h").val(height);
          $("#six_cp_w").val(width);
          $("#six_cp_q").val(qty);
          $("#six_cp_sheet").val(sheet);
          $(".six_cp_cal").val(sth_cp_cal);
          $(".six_cp_WBS").val(sth_cp_WBS);

          $("#sth_cp_h").val("");
          $("#sth_cp_w").val("");
          $("#sth_cp_q").val("");
          $("#sth_cp_sheet").val("");
          $(".sth_cp_cal").val("");
          $(".sth_cp_WBS").val(""); 
          $("#sixth_cp").show();              
        }

        if($(".e_cp_WBS").val() != "")
        {
          let height = document.getElementById("e_cp_h").value;
          let width = document.getElementById("e_cp_w").value;
          let qty = document.getElementById("e_cp_q").value;
          let sheet = document.getElementById("e_cp_sheet").value;
          let e_cp_cal = $(".e_cp_cal").val();
          let e_cp_WBS = $(".e_cp_WBS").val();

          $("#sth_cp_h").val(height);
          $("#sth_cp_w").val(width);
          $("#sth_cp_q").val(qty);
          $("#sth_cp_sheet").val(sheet);
          $(".sth_cp_cal").val(e_cp_cal);
          $(".sth_cp_WBS").val(e_cp_WBS);

          $("#e_cp_h").val("");
          $("#e_cp_w").val("");
          $("#e_cp_q").val("");
          $("#e_cp_sheet").val("");
          $(".e_cp_cal").val("");
          $(".e_cp_WBS").val("");
          $("#seventh_cp").show();               
        }

        if($(".n_cp_WBS").val() != "")
        {
          let height = document.getElementById("n_cp_h").value;
          let width = document.getElementById("n_cp_w").value;
          let qty = document.getElementById("n_cp_q").value;
          let sheet = document.getElementById("n_cp_sheet").value;
          let n_cp_cal = $(".n_cp_cal").val();
          let n_cp_WBS = $(".n_cp_WBS").val();

          $("#e_cp_h").val(height);
          $("#e_cp_w").val(width);
          $("#e_cp_q").val(qty);
          $("#e_cp_sheet").val(sheet);
          $(".e_cp_cal").val(n_cp_cal);
          $(".e_cp_WBS").val(n_cp_WBS);

          $("#n_cp_h").val("");
          $("#n_cp_w").val("");
          $("#n_cp_q").val("");
          $("#n_cp_sheet").val("");
          $(".n_cp_cal").val("");
          $(".n_cp_WBS").val(""); 
          $("#eighth_cp").show();              
        }
        }
        else{
        $("#s_cp_h").val("");
        $("#s_cp_w").val("");
        $("#s_cp_q").val("");
        $("#s_cp_sheet").val("");
        $(".s_cp_WBS").val("");
        $(".s_cp_cal").val("");
        }
       
      },
    });
});  

$(document).on("click", "#t_cp_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("t_cp_h").value;
  let width = document.getElementById("t_cp_w").value;
  let qty = document.getElementById("t_cp_q").value;
  let sheet = document.getElementById("t_cp_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/t_cp.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".t_cp_cal").val(respData.square_feet);
        $(".t_cp_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".t_cp_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/t_cp_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
        $("#fourth_cp").hide();
        $("#fifth_cp").hide();
        $("#sixth_cp").hide();
        $("#seventh_cp").hide();
        $("#eighth_cp").hide();
        $("#ninth_cp").hide();
        if($(".f_cp_WBS").val() != "")
        {
          let height = document.getElementById("f_cp_h").value;
          let width = document.getElementById("f_cp_w").value;
          let qty = document.getElementById("f_cp_q").value;
          let sheet = document.getElementById("f_cp_sheet").value;
          let f_cp_cal = $(".f_cp_cal").val();
          let f_cp_WBS = $(".f_cp_WBS").val();

          $("#t_cp_h").val(height);
          $("#t_cp_w").val(width);
          $("#t_cp_q").val(qty);
          $("#t_cp_sheet").val(sheet);
          $(".t_cp_cal").val(f_cp_cal);
          $(".t_cp_WBS").val(f_cp_WBS);

          $("#f_cp_h").val("");
          $("#f_cp_w").val("");
          $("#f_cp_q").val("");
          $("#f_cp_sheet").val("");
          $(".f_cp_cal").val("");
          $(".f_cp_WBS").val("");  
          $("#third_cp").show();                
        

        if($(".fth_cp_WBS").val() != "")
        {
          let height = document.getElementById("fth_cp_h").value;
          let width = document.getElementById("fth_cp_w").value;
          let qty = document.getElementById("fth_cp_q").value;
          let sheet = document.getElementById("fth_cp_sheet").value;
          let fth_cp_cal = $(".fth_cp_cal").val();
          let fth_cp_WBS = $(".fth_cp_WBS").val();

          $("#f_cp_h").val(height);
          $("#f_cp_w").val(width);
          $("#f_cp_q").val(qty);
          $("#f_cp_sheet").val(sheet);
          $(".f_cp_cal").val(fth_cp_cal);
          $(".f_cp_WBS").val(fth_cp_WBS);

          $("#fth_cp_h").val("");
          $("#fth_cp_w").val("");
          $("#fth_cp_q").val("");
          $("#fth_cp_sheet").val("");
          $(".fth_cp_cal").val("");
          $(".fth_cp_WBS").val("");
          $("#fourth_cp").show();               
        }

        if($(".six_cp_WBS").val() != "")
        {
          let height = document.getElementById("six_cp_h").value;
          let width = document.getElementById("six_cp_w").value;
          let qty = document.getElementById("six_cp_q").value;
          let sheet = document.getElementById("six_cp_sheet").value;
          let six_cp_cal = $(".six_cp_cal").val();
          let six_cp_WBS = $(".six_cp_WBS").val();

          $("#fth_cp_h").val(height);
          $("#fth_cp_w").val(width);
          $("#fth_cp_q").val(qty);
          $("#fth_cp_sheet").val(sheet);
          $(".fth_cp_cal").val(six_cp_cal);
          $(".fth_cp_WBS").val(six_cp_WBS);

          $("#six_cp_h").val("");
          $("#six_cp_w").val("");
          $("#six_cp_q").val("");
          $("#six_cp_sheet").val("");
          $(".six_cp_cal").val("");
          $(".six_cp_WBS").val(""); 
          $("#fifth_cp").show();                
        }

        if($(".sth_cp_WBS").val() != "")
        {
          let height = document.getElementById("sth_cp_h").value;
          let width = document.getElementById("sth_cp_w").value;
          let qty = document.getElementById("sth_cp_q").value;
          let sheet = document.getElementById("sth_cp_sheet").value;
          let sth_cp_cal = $(".sth_cp_cal").val();
          let sth_cp_WBS = $(".sth_cp_WBS").val();

          $("#six_cp_h").val(height);
          $("#six_cp_w").val(width);
          $("#six_cp_q").val(qty);
          $("#six_cp_sheet").val(sheet);
          $(".six_cp_cal").val(sth_cp_cal);
          $(".six_cp_WBS").val(sth_cp_WBS);

          $("#sth_cp_h").val("");
          $("#sth_cp_w").val("");
          $("#sth_cp_q").val("");
          $("#sth_cp_sheet").val("");
          $(".sth_cp_cal").val("");
          $(".sth_cp_WBS").val(""); 
          $("#sixth_cp").show();              
        }

        if($(".e_cp_WBS").val() != "")
        {
          let height = document.getElementById("e_cp_h").value;
          let width = document.getElementById("e_cp_w").value;
          let qty = document.getElementById("e_cp_q").value;
          let sheet = document.getElementById("e_cp_sheet").value;
          let e_cp_cal = $(".e_cp_cal").val();
          let e_cp_WBS = $(".e_cp_WBS").val();

          $("#sth_cp_h").val(height);
          $("#sth_cp_w").val(width);
          $("#sth_cp_q").val(qty);
          $("#sth_cp_sheet").val(sheet);
          $(".sth_cp_cal").val(e_cp_cal);
          $(".sth_cp_WBS").val(e_cp_WBS);

          $("#e_cp_h").val("");
          $("#e_cp_w").val("");
          $("#e_cp_q").val("");
          $("#e_cp_sheet").val("");
          $(".e_cp_cal").val("");
          $(".e_cp_WBS").val("");
          $("#seventh_cp").show();               
        }

        if($(".n_cp_WBS").val() != "")
        {
          let height = document.getElementById("n_cp_h").value;
          let width = document.getElementById("n_cp_w").value;
          let qty = document.getElementById("n_cp_q").value;
          let sheet = document.getElementById("n_cp_sheet").value;
          let n_cp_cal = $(".n_cp_cal").val();
          let n_cp_WBS = $(".n_cp_WBS").val();

          $("#e_cp_h").val(height);
          $("#e_cp_w").val(width);
          $("#e_cp_q").val(qty);
          $("#e_cp_sheet").val(sheet);
          $(".e_cp_cal").val(n_cp_cal);
          $(".e_cp_WBS").val(n_cp_WBS);

          $("#n_cp_h").val("");
          $("#n_cp_w").val("");
          $("#n_cp_q").val("");
          $("#n_cp_sheet").val("");
          $(".n_cp_cal").val("");
          $(".n_cp_WBS").val(""); 
          $("#eighth_cp").show();              
        }
        }
        else{
        $("#t_cp_h").val("");
       $("#t_cp_w").val("");
       $("#t_cp_q").val("");
      $("#t_cp_sheet").val("");
      $(".t_cp_WBS").val("");
      $(".t_cp_cal").val("");
        }
       
       
      },
    });
}); 

$(document).on("click", "#f_cp_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("f_cp_h").value;
  let width = document.getElementById("f_cp_w").value;
  let qty = document.getElementById("f_cp_q").value;
  let sheet = document.getElementById("f_cp_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/f_cp.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".f_cp_cal").val(respData.square_feet);
        $(".f_cp_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".f_cp_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/f_cp_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
        $("#fifth_cp").hide();
        $("#sixth_cp").hide();
        $("#seventh_cp").hide();
        $("#eighth_cp").hide();
        $("#ninth_cp").hide();
        if($(".fth_cp_WBS").val() != "")
        {
          let height = document.getElementById("fth_cp_h").value;
          let width = document.getElementById("fth_cp_w").value;
          let qty = document.getElementById("fth_cp_q").value;
          let sheet = document.getElementById("fth_cp_sheet").value;
          let fth_cp_cal = $(".fth_cp_cal").val();
          let fth_cp_WBS = $(".fth_cp_WBS").val();

          $("#f_cp_h").val(height);
          $("#f_cp_w").val(width);
          $("#f_cp_q").val(qty);
          $("#f_cp_sheet").val(sheet);
          $(".f_cp_cal").val(fth_cp_cal);
          $(".f_cp_WBS").val(fth_cp_WBS);

          $("#fth_cp_h").val("");
          $("#fth_cp_w").val("");
          $("#fth_cp_q").val("");
          $("#fth_cp_sheet").val("");
          $(".fth_cp_cal").val("");
          $(".fth_cp_WBS").val("");
          $("#fourth_cp").show();               
        

        if($(".six_cp_WBS").val() != "")
        {
          let height = document.getElementById("six_cp_h").value;
          let width = document.getElementById("six_cp_w").value;
          let qty = document.getElementById("six_cp_q").value;
          let sheet = document.getElementById("six_cp_sheet").value;
          let six_cp_cal = $(".six_cp_cal").val();
          let six_cp_WBS = $(".six_cp_WBS").val();

          $("#fth_cp_h").val(height);
          $("#fth_cp_w").val(width);
          $("#fth_cp_q").val(qty);
          $("#fth_cp_sheet").val(sheet);
          $(".fth_cp_cal").val(six_cp_cal);
          $(".fth_cp_WBS").val(six_cp_WBS);

          $("#six_cp_h").val("");
          $("#six_cp_w").val("");
          $("#six_cp_q").val("");
          $("#six_cp_sheet").val("");
          $(".six_cp_cal").val("");
          $(".six_cp_WBS").val(""); 
          $("#fifth_cp").show();                
        }

        if($(".sth_cp_WBS").val() != "")
        {
          let height = document.getElementById("sth_cp_h").value;
          let width = document.getElementById("sth_cp_w").value;
          let qty = document.getElementById("sth_cp_q").value;
          let sheet = document.getElementById("sth_cp_sheet").value;
          let sth_cp_cal = $(".sth_cp_cal").val();
          let sth_cp_WBS = $(".sth_cp_WBS").val();

          $("#six_cp_h").val(height);
          $("#six_cp_w").val(width);
          $("#six_cp_q").val(qty);
          $("#six_cp_sheet").val(sheet);
          $(".six_cp_cal").val(sth_cp_cal);
          $(".six_cp_WBS").val(sth_cp_WBS);

          $("#sth_cp_h").val("");
          $("#sth_cp_w").val("");
          $("#sth_cp_q").val("");
          $("#sth_cp_sheet").val("");
          $(".sth_cp_cal").val("");
          $(".sth_cp_WBS").val(""); 
          $("#sixth_cp").show();              
        }

        if($(".e_cp_WBS").val() != "")
        {
          let height = document.getElementById("e_cp_h").value;
          let width = document.getElementById("e_cp_w").value;
          let qty = document.getElementById("e_cp_q").value;
          let sheet = document.getElementById("e_cp_sheet").value;
          let e_cp_cal = $(".e_cp_cal").val();
          let e_cp_WBS = $(".e_cp_WBS").val();

          $("#sth_cp_h").val(height);
          $("#sth_cp_w").val(width);
          $("#sth_cp_q").val(qty);
          $("#sth_cp_sheet").val(sheet);
          $(".sth_cp_cal").val(e_cp_cal);
          $(".sth_cp_WBS").val(e_cp_WBS);

          $("#e_cp_h").val("");
          $("#e_cp_w").val("");
          $("#e_cp_q").val("");
          $("#e_cp_sheet").val("");
          $(".e_cp_cal").val("");
          $(".e_cp_WBS").val("");
          $("#seventh_cp").show();               
        }

        if($(".n_cp_WBS").val() != "")
        {
          let height = document.getElementById("n_cp_h").value;
          let width = document.getElementById("n_cp_w").value;
          let qty = document.getElementById("n_cp_q").value;
          let sheet = document.getElementById("n_cp_sheet").value;
          let n_cp_cal = $(".n_cp_cal").val();
          let n_cp_WBS = $(".n_cp_WBS").val();

          $("#e_cp_h").val(height);
          $("#e_cp_w").val(width);
          $("#e_cp_q").val(qty);
          $("#e_cp_sheet").val(sheet);
          $(".e_cp_cal").val(n_cp_cal);
          $(".e_cp_WBS").val(n_cp_WBS);

          $("#n_cp_h").val("");
          $("#n_cp_w").val("");
          $("#n_cp_q").val("");
          $("#n_cp_sheet").val("");
          $(".n_cp_cal").val("");
          $(".n_cp_WBS").val(""); 
          $("#eighth_cp").show();              
        }
        }
        else{
        $("#f_cp_h").val("");
        $("#f_cp_w").val("");
        $("#f_cp_q").val("");
        $("#f_cp_sheet").val("");
        $(".f_cp_WBS").val("");
        $(".f_cp_cal").val("");
        }
       
      },
    });
}); 

$(document).on("click", "#fth_cp_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("fth_cp_h").value;
  let width = document.getElementById("fth_cp_w").value;
  let qty = document.getElementById("fth_cp_q").value;
  let sheet = document.getElementById("fth_cp_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/fth_cp.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".fth_cp_cal").val(respData.square_feet);
        $(".fth_cp_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".fth_cp_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/fth_cp_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
        $("#sixth_cp").hide();
        $("#seventh_cp").hide();
        $("#eighth_cp").hide();
        $("#ninth_cp").hide();
        if($(".six_cp_WBS").val() != "")
        {
          let height = document.getElementById("six_cp_h").value;
          let width = document.getElementById("six_cp_w").value;
          let qty = document.getElementById("six_cp_q").value;
          let sheet = document.getElementById("six_cp_sheet").value;
          let six_cp_cal = $(".six_cp_cal").val();
          let six_cp_WBS = $(".six_cp_WBS").val();

          $("#fth_cp_h").val(height);
          $("#fth_cp_w").val(width);
          $("#fth_cp_q").val(qty);
          $("#fth_cp_sheet").val(sheet);
          $(".fth_cp_cal").val(six_cp_cal);
          $(".fth_cp_WBS").val(six_cp_WBS);

          $("#six_cp_h").val("");
          $("#six_cp_w").val("");
          $("#six_cp_q").val("");
          $("#six_cp_sheet").val("");
          $(".six_cp_cal").val("");
          $(".six_cp_WBS").val(""); 
          $("#fifth_cp").show();                
        

        if($(".sth_cp_WBS").val() != "")
        {
          let height = document.getElementById("sth_cp_h").value;
          let width = document.getElementById("sth_cp_w").value;
          let qty = document.getElementById("sth_cp_q").value;
          let sheet = document.getElementById("sth_cp_sheet").value;
          let sth_cp_cal = $(".sth_cp_cal").val();
          let sth_cp_WBS = $(".sth_cp_WBS").val();

          $("#six_cp_h").val(height);
          $("#six_cp_w").val(width);
          $("#six_cp_q").val(qty);
          $("#six_cp_sheet").val(sheet);
          $(".six_cp_cal").val(sth_cp_cal);
          $(".six_cp_WBS").val(sth_cp_WBS);

          $("#sth_cp_h").val("");
          $("#sth_cp_w").val("");
          $("#sth_cp_q").val("");
          $("#sth_cp_sheet").val("");
          $(".sth_cp_cal").val("");
          $(".sth_cp_WBS").val(""); 
          $("#sixth_cp").show();              
        }

        if($(".e_cp_WBS").val() != "")
        {
          let height = document.getElementById("e_cp_h").value;
          let width = document.getElementById("e_cp_w").value;
          let qty = document.getElementById("e_cp_q").value;
          let sheet = document.getElementById("e_cp_sheet").value;
          let e_cp_cal = $(".e_cp_cal").val();
          let e_cp_WBS = $(".e_cp_WBS").val();

          $("#sth_cp_h").val(height);
          $("#sth_cp_w").val(width);
          $("#sth_cp_q").val(qty);
          $("#sth_cp_sheet").val(sheet);
          $(".sth_cp_cal").val(e_cp_cal);
          $(".sth_cp_WBS").val(e_cp_WBS);

          $("#e_cp_h").val("");
          $("#e_cp_w").val("");
          $("#e_cp_q").val("");
          $("#e_cp_sheet").val("");
          $(".e_cp_cal").val("");
          $(".e_cp_WBS").val("");
          $("#seventh_cp").show();               
        }

        if($(".n_cp_WBS").val() != "")
        {
          let height = document.getElementById("n_cp_h").value;
          let width = document.getElementById("n_cp_w").value;
          let qty = document.getElementById("n_cp_q").value;
          let sheet = document.getElementById("n_cp_sheet").value;
          let n_cp_cal = $(".n_cp_cal").val();
          let n_cp_WBS = $(".n_cp_WBS").val();

          $("#e_cp_h").val(height);
          $("#e_cp_w").val(width);
          $("#e_cp_q").val(qty);
          $("#e_cp_sheet").val(sheet);
          $(".e_cp_cal").val(n_cp_cal);
          $(".e_cp_WBS").val(n_cp_WBS);

          $("#n_cp_h").val("");
          $("#n_cp_w").val("");
          $("#n_cp_q").val("");
          $("#n_cp_sheet").val("");
          $(".n_cp_cal").val("");
          $(".n_cp_WBS").val(""); 
          $("#eighth_cp").show();              
        }
        }
        else{
          $("#fth_cp_h").val("");
          $("#fth_cp_w").val("");
          $("#fth_cp_q").val("");
          $("#fth_cp_sheet").val("");
          $(".fth_cp_WBS").val("");
          $(".fth_cp_cal").val("");
        }
       
      },
    });
}); 

$(document).on("click", "#six_cp_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("six_cp_h").value;
  let width = document.getElementById("six_cp_w").value;
  let qty = document.getElementById("six_cp_q").value;
  let sheet = document.getElementById("six_cp_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/six_cp.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".six_cp_cal").val(respData.square_feet);
        $(".six_cp_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".six_cp_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/six_cp_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
        $("#seventh_cp").hide();
        $("#eighth_cp").hide();
        $("#ninth_cp").hide();
        if($(".sth_cp_WBS").val() != "")
        {
          let height = document.getElementById("sth_cp_h").value;
          let width = document.getElementById("sth_cp_w").value;
          let qty = document.getElementById("sth_cp_q").value;
          let sheet = document.getElementById("sth_cp_sheet").value;
          let sth_cp_cal = $(".sth_cp_cal").val();
          let sth_cp_WBS = $(".sth_cp_WBS").val();

          $("#six_cp_h").val(height);
          $("#six_cp_w").val(width);
          $("#six_cp_q").val(qty);
          $("#six_cp_sheet").val(sheet);
          $(".six_cp_cal").val(sth_cp_cal);
          $(".six_cp_WBS").val(sth_cp_WBS);

          $("#sth_cp_h").val("");
          $("#sth_cp_w").val("");
          $("#sth_cp_q").val("");
          $("#sth_cp_sheet").val("");
          $(".sth_cp_cal").val("");
          $(".sth_cp_WBS").val(""); 
          $("#sixth_cp").show();              
        

        if($(".e_cp_WBS").val() != "")
        {
          let height = document.getElementById("e_cp_h").value;
          let width = document.getElementById("e_cp_w").value;
          let qty = document.getElementById("e_cp_q").value;
          let sheet = document.getElementById("e_cp_sheet").value;
          let e_cp_cal = $(".e_cp_cal").val();
          let e_cp_WBS = $(".e_cp_WBS").val();

          $("#sth_cp_h").val(height);
          $("#sth_cp_w").val(width);
          $("#sth_cp_q").val(qty);
          $("#sth_cp_sheet").val(sheet);
          $(".sth_cp_cal").val(e_cp_cal);
          $(".sth_cp_WBS").val(e_cp_WBS);

          $("#e_cp_h").val("");
          $("#e_cp_w").val("");
          $("#e_cp_q").val("");
          $("#e_cp_sheet").val("");
          $(".e_cp_cal").val("");
          $(".e_cp_WBS").val("");
          $("#seventh_cp").show();               
        }

        if($(".n_cp_WBS").val() != "")
        {
          let height = document.getElementById("n_cp_h").value;
          let width = document.getElementById("n_cp_w").value;
          let qty = document.getElementById("n_cp_q").value;
          let sheet = document.getElementById("n_cp_sheet").value;
          let n_cp_cal = $(".n_cp_cal").val();
          let n_cp_WBS = $(".n_cp_WBS").val();

          $("#e_cp_h").val(height);
          $("#e_cp_w").val(width);
          $("#e_cp_q").val(qty);
          $("#e_cp_sheet").val(sheet);
          $(".e_cp_cal").val(n_cp_cal);
          $(".e_cp_WBS").val(n_cp_WBS);

          $("#n_cp_h").val("");
          $("#n_cp_w").val("");
          $("#n_cp_q").val("");
          $("#n_cp_sheet").val("");
          $(".n_cp_cal").val("");
          $(".n_cp_WBS").val(""); 
          $("#eighth_cp").show();              
        }
        }
        else{
          $("#six_cp_h").val("");
          $("#six_cp_w").val("");
          $("#six_cp_q").val("");
          $("#six_cp_sheet").val("");
          $(".six_cp_WBS").val("");
          $(".six_cp_cal").val("");
        }
       
      },
    });
}); 

$(document).on("click", "#sth_cp_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("sth_cp_h").value;
  let width = document.getElementById("sth_cp_w").value;
  let qty = document.getElementById("sth_cp_q").value;
  let sheet = document.getElementById("sth_cp_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/sth_cp.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".sth_cp_cal").val(respData.square_feet);
        $(".sth_cp_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".sth_cp_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/sth_cp_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
        $("#eighth_cp").hide();
        $("#ninth_cp").hide();
        if($(".e_cp_WBS").val() != "")
        {
          let height = document.getElementById("e_cp_h").value;
          let width = document.getElementById("e_cp_w").value;
          let qty = document.getElementById("e_cp_q").value;
          let sheet = document.getElementById("e_cp_sheet").value;
          let e_cp_cal = $(".e_cp_cal").val();
          let e_cp_WBS = $(".e_cp_WBS").val();

          $("#sth_cp_h").val(height);
          $("#sth_cp_w").val(width);
          $("#sth_cp_q").val(qty);
          $("#sth_cp_sheet").val(sheet);
          $(".sth_cp_cal").val(e_cp_cal);
          $(".sth_cp_WBS").val(e_cp_WBS);

          $("#e_cp_h").val("");
          $("#e_cp_w").val("");
          $("#e_cp_q").val("");
          $("#e_cp_sheet").val("");
          $(".e_cp_cal").val("");
          $(".e_cp_WBS").val("");               
        

        if($(".n_cp_WBS").val() != "")
        {
          let height = document.getElementById("n_cp_h").value;
          let width = document.getElementById("n_cp_w").value;
          let qty = document.getElementById("n_cp_q").value;
          let sheet = document.getElementById("n_cp_sheet").value;
          let n_cp_cal = $(".n_cp_cal").val();
          let n_cp_WBS = $(".n_cp_WBS").val();

          $("#e_cp_h").val(height);
          $("#e_cp_w").val(width);
          $("#e_cp_q").val(qty);
          $("#e_cp_sheet").val(sheet);
          $(".e_cp_cal").val(n_cp_cal);
          $(".e_cp_WBS").val(n_cp_WBS);

          $("#n_cp_h").val("");
          $("#n_cp_w").val("");
          $("#n_cp_q").val("");
          $("#n_cp_sheet").val("");
          $(".n_cp_cal").val("");
          $(".n_cp_WBS").val(""); 
          $("#eighth_cp").show();              
        }
        }
        else{
          $("#sth_cp_h").val("");
          $("#sth_cp_w").val("");
          $("#sth_cp_q").val("");
          $("#sth_cp_sheet").val("");
          $(".sth_cp_WBS").val("");
          $(".sth_cp_cal").val("");
         }
       
      },
    });
}); 

$(document).on("click", "#e_cp_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("e_cp_h").value;
  let width = document.getElementById("e_cp_w").value;
  let qty = document.getElementById("e_cp_q").value;
  let sheet = document.getElementById("e_cp_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/e_cp.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".e_cp_cal").val(respData.square_feet);
        $(".e_cp_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".e_cp_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/e_cp_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
        $("#ninth_cp").hide();
        if($(".n_cp_WBS").val() != "")
        {
          let height = document.getElementById("n_cp_h").value;
          let width = document.getElementById("n_cp_w").value;
          let qty = document.getElementById("n_cp_q").value;
          let sheet = document.getElementById("n_cp_sheet").value;
          let n_cp_cal = $(".n_cp_cal").val();
          let n_cp_WBS = $(".n_cp_WBS").val();

          $("#e_cp_h").val(height);
          $("#e_cp_w").val(width);
          $("#e_cp_q").val(qty);
          $("#e_cp_sheet").val(sheet);
          $(".e_cp_cal").val(n_cp_cal);
          $(".e_cp_WBS").val(n_cp_WBS);

          $("#n_cp_h").val("");
          $("#n_cp_w").val("");
          $("#n_cp_q").val("");
          $("#n_cp_sheet").val("");
          $(".n_cp_cal").val("");
          $(".n_cp_WBS").val(""); 
          $("#eighth_cp").show();              
        }
        
        else{
          $("#e_cp_h").val("");
          $("#e_cp_w").val("");
          $("#e_cp_q").val("");
          $("#e_cp_sheet").val("");
          $(".e_cp_WBS").val("");
          $(".e_cp_cal").val("");
         }
       
      },
    });
}); 

$(document).on("click", "#n_cp_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("n_cp_h").value;
  let width = document.getElementById("n_cp_w").value;
  let qty = document.getElementById("n_cp_q").value;
  let sheet = document.getElementById("n_cp_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/n_cp.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".n_cp_cal").val(respData.square_feet);
        $(".n_cp_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".n_cp_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/n_cp_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
       $("#n_cp_h").val("");
       $("#n_cp_w").val("");
       $("#n_cp_q").val("");
      $("#n_cp_sheet").val("");
      $(".n_cp_WBS").val("");
      $(".n_cp_cal").val("");

        $("#ninth_cp").hide();
      },
    });
}); 

// For calcuation of pallet
$(document).on("click", "#p_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("p_h").value;
  let width = document.getElementById("p_w").value;
  let qty = document.getElementById("p_q").value;
  let sheet = document.getElementById("p_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/pallet.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".p_cal").val(respData.square_feet);
        $(".p_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".p_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/p_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
        if($(".s_p_WBS").val() != "")
        {
          let height = document.getElementById("s_p_h").value;
          let width = document.getElementById("s_p_w").value;
          let qty = document.getElementById("s_p_q").value;
          let sheet = document.getElementById("s_p_sheet").value;
          let s_p_cal = $(".s_p_cal").val();
          let s_p_WBS = $(".s_p_WBS").val();

          $("#p_h").val(height);
          $("#p_w").val(width);
          $("#p_q").val(qty);
          $("#p_sheet").val(sheet);
          $(".p_cal").val(s_p_cal);
          $(".p_WBS").val(s_p_WBS);

          $("#s_p_h").val("");
          $("#s_p_w").val("");
          $("#s_p_q").val("");
          $("#s_p_sheet").val("");
          $(".s_p_cal").val("");
          $(".s_p_WBS").val("");               
        }
        else{
        $("#p_h").val("");
        $("#p_w").val("");
        $("#p_q").val("");
        $("#p_sheet").val("");
        $(".p_cal").val("");
        $(".p_WBS").val("");
        }
      },
    });
}); 
$(document).on("click", "#s_p_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("s_p_h").value;
  let width = document.getElementById("s_p_w").value;
  let qty = document.getElementById("s_p_q").value;
  let sheet = document.getElementById("s_p_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/s_pallet.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".s_p_cal").val(respData.square_feet);
        $(".s_p_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});

$(document).on("click", ".s_p_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/s_p_del.php",
      method: "POST",
      data: {
        pannel_costing_id
      },
      success: function (data){
        $("#s_p_h").val("");
        $("#s_p_w").val("");
        $("#s_p_q").val("");
        $("#s_p_sheet").val("");
        $(".s_p_cal").val("");
        $(".s_p_WBS").val("");
      },
    });
}); 

// For calcuation of Front and back base
$(document).on("click", "#f-b-cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("front_back_h").value;
  let width = document.getElementById("front_back_w").value;
  let qty = document.getElementById("front_back_q").value;
  let sheet = document.getElementById("front_back_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/front_back.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".front_back_cal").val(respData.square_feet);
        $(".front_back_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".f_b_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/front_back_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#front_back_h").val("");
        $("#front_back_w").val("");
        $("#front_back_q").val("");
        $("#front_back_sheet").val("");
        $(".front_back_WBS").val("");
        $(".front_back_cal").val("");
      },
    });
    $("#ten").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of Right and left base
$(document).on("click", "#right_left_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("right_left_h").value;
  let width = document.getElementById("right_left_w").value;
  let qty = document.getElementById("right_left_q").value;
  let sheet = document.getElementById("right_left_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/right_left.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".right_left_cal").val(respData.square_feet);
        $(".right_left_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".right_left_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/right_left_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#right_left_h").val("");
        $("#right_left_w").val("");
        $("#right_left_q").val("");
        $("#right_left_sheet").val("");
        $(".right_left_WBS").val("");
        $(".right_left_cal").val("");
      },
    });
    $("#eleven").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of vertical inside U
$(document).on("click", "#vertical_inside_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("vertical_inside_h").value;
  let width = document.getElementById("vertical_inside_w").value;
  let qty = document.getElementById("vertical_inside_q").value;
  let sheet = document.getElementById("vertical_inside_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/vertical_inside.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".vertical_inside_cal").val(respData.square_feet);
        $(".vertical_inside_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".vertical_inside_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/vertical_inside_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#vertical_inside_h").val("");
        $("#vertical_inside_w").val("");
        $("#vertical_inside_q").val("");
        $("#vertical_inside_sheet").val("");
        $(".vertical_inside_WBS").val("");
        $(".vertical_inside_cal").val("");
      },
    });
    $("#twelve").css("background-color", "rgb(187, 187, 187)");
});


// For calcuation of horizontal inside U
$(document).on("click", "#horizonal_inside_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("horizonal_inside_h").value;
  let width = document.getElementById("horizonal_inside_w").value;
  let qty = document.getElementById("horizonal_inside_q").value;
  let sheet = document.getElementById("horizonal_inside_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/horizonal_inside.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".horizonal_inside_cal").val(respData.square_feet);
        $(".horizonal_inside_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".horizonal_inside_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/horizonal_inside_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#horizonal_inside_h").val("");
        $("#horizonal_inside_w").val("");
        $("#horizonal_inside_q").val("");
        $("#horizonal_inside_sheet").val("");
        $(".horizonal_inside_WBS").val("");
        $(".horizonal_inside_cal").val("");
      },
    });
    $("#thirteen").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of Vertical_inside_piece L type
$(document).on("click", "#Vertical_inside_piece_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("Vertical_inside_piece_h").value;
  let width = document.getElementById("Vertical_inside_piece_w").value;
  let qty = document.getElementById("Vertical_inside_piece_q").value;
  let sheet = document.getElementById("Vertical_inside_piece_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/Vertical_inside_piece.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".Vertical_inside_piece_cal").val(respData.square_feet);
        $(".Vertical_inside_piece_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".Vertical_inside_piece_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/Vertical_inside_piece_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#Vertical_inside_piece_h").val("");
        $("#Vertical_inside_piece_w").val("");
        $("#Vertical_inside_piece_q").val("");
        $("#Vertical_inside_piece_sheet").val("");
        $(".Vertical_inside_piece_WBS").val("");
        $(".Vertical_inside_piece_cal").val("");
      },
    });
    $("#fourteen").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of Horizental inside peace U type
$(document).on("click", "#horizental_inside_piece_U_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("horizental_inside_piece_U_h").value;
  let width = document.getElementById("horizental_inside_piece_U_w").value;
  let qty = document.getElementById("horizental_inside_piece_U_q").value;
  let sheet = document.getElementById("horizental_inside_piece_U_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/horizental_inside_piece_U.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".horizental_inside_piece_U_cal").val(respData.square_feet);
        $(".horizental_inside_piece_U_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".horizental_inside_piece_U_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/horizental_inside_piece_U_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#horizental_inside_piece_U_h").val("");
        $("#horizental_inside_piece_U_w").val("");
        $("#horizental_inside_piece_U_q").val("");
        $("#horizental_inside_piece_U_sheet").val("");
        $(".horizental_inside_piece_U_WBS").val("");
        $(".horizental_inside_piece_U_cal").val("");
      },
    });
    $("#fifteen").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of top
$(document).on("click", "#top_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("top_h").value;
  let width = document.getElementById("top_w").value;
  let qty = document.getElementById("top_q").value;
  let sheet = document.getElementById("top_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/top.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".top_cal").val(respData.square_feet);
        $(".top_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".top_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/top_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#top_h").val("");
        $("#top_w").val("");
        $("#top_q").val("");
        $("#top_sheet").val("");
        $(".top_WBS").val("");
        $(".top_cal").val("");
      },
    });
    $("#sixteen").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of bottom
$(document).on("click", "#bottom_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("bottom_h").value;
  let width = document.getElementById("bottom_w").value;
  let qty = document.getElementById("bottom_q").value;
  let sheet = document.getElementById("bottom_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/bottom.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".bottom_cal").val(respData.square_feet);
        $(".bottom_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".bottom_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/bottom_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#bottom_h").val("");
        $("#bottom_w").val("");
        $("#bottom_q").val("");
        $("#bottom_sheet").val("");
        $(".bottom_WBS").val("");
        $(".bottom_cal").val("");
      },
    });
    $("#seventeen").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of portion sheet
$(document).on("click", "#p_s_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("p_s_h").value;
  let width = document.getElementById("p_s_w").value;
  let qty = document.getElementById("p_s_q").value;
  let sheet = document.getElementById("p_s_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/portion_sheet.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".p_s_cal").val(respData.square_feet);
        $(".p_s_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});


$(document).on("click", ".ps_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/p_s_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#p_s_h").val("");
        $("#p_s_w").val("");
        $("#p_s_q").val("");
        $("#p_s_sheet").val("");
        $(".p_s_WBS").val("");
        $(".p_s_cal").val("");
      },
    });
    $("#eighteen").css("background-color", "rgb(187, 187, 187)");
});


$(document).on("click", "#s_p_s_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("s_p_s_h").value;
  let width = document.getElementById("s_p_s_w").value;
  let qty = document.getElementById("s_p_s_q").value;
  let sheet = document.getElementById("s_p_s_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/s_portion_sheet.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".s_p_s_cal").val(respData.square_feet);
        $(".s_p_s_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});

$(document).on("click", ".s_ps_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/s_p_s_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#s_p_s_h").val("");
        $("#s_p_s_w").val("");
        $("#s_p_s_q").val("");
        $("#s_p_s_sheet").val("");
        $(".s_p_s_WBS").val("");
        $(".s_p_s_cal").val("");
      },
    });
    $("#eighteen").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of Miscellaneous
$(document).on("click", "#miscellaneous_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let height = document.getElementById("miscellaneous_h").value;
  let width = document.getElementById("miscellaneous_w").value;
  let qty = document.getElementById("miscellaneous_q").value;
  let sheet = document.getElementById("miscellaneous_sheet").value;
  if (sheet != "") {
  } else {
    sheet = "0";
  }
  if (qty != "" && width != "" && height != "") {
    $.ajax({
      url: "cost/miscellaneous.php",
      method: "POST",
      data: {
        height: height,
        width: width,
        qty: qty,
        sheet: sheet,
        pannel_costing_id,
        pannel_costing_id,
      },
      success: function (data) {
        var respData = JSON.parse(data);
        $(".miscellaneous_cal").val(respData.square_feet);
        $(".miscellaneous_WBS").val(respData.WBS);
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".miscellaneous_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/miscellaneous_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#miscellaneous_h").val("");
        $("#miscellaneous_w").val("");
        $("#miscellaneous_q").val("");
        $("#miscellaneous_sheet").val("");
        $(".miscellaneous_WBS").val("");
        $(".miscellaneous_cal").val("");
      },
    });
    $("#ninteen").css("background-color", "rgb(187, 187, 187)");
});

$(document).on("click", "#sheet_selection", function () {
  let pannel_costing_id = $(".pannel_costing_id").val();
  let sheet_type = document.getElementById("sheet_type").value;
  let matal_s_price = document.getElementById("matal_s_price").value;
  let stainless_s_price = document.getElementById("stainless_s_price").value;
  let galvanized_s_price = document.getElementById("galvanized_s_price").value;

  if (sheet_type != "") {
    $.ajax({
      url: "cost/sheet_selection.php",
      method: "POST",
      data: {
        pannel_costing_id: pannel_costing_id,
        sheet_type: sheet_type,
        matal_s_price: matal_s_price,
        stainless_s_price: stainless_s_price,
        galvanized_s_price,
        galvanized_s_price,
      },
    });
    $("#twenty").css("background-color", "rgb(13, 211, 102)");
    nextPrev(1);
  } else {
    Swal.fire("Please Select Sheet Type First");
  }
});
$(document).on("click", ".sheet_selection_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/sheet_selection_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#sheet_type").val("");
        $("#matal_s_price").val("");
        $("#stainless_s_price").val("");
        $("#galvanized_s_price").val("");
      },
    });
    $("#twenty").css("background-color", "rgb(187, 187, 187)");
});

// For Total calculation of pannel
$(document).on("click", "#total_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let bus_bar_price = document.getElementById("bus_bar_price").value;
  let sheet_type = document.getElementById("sheet_type").value;
  if (sheet_type != "") {
    if (bus_bar_price != "") {
      let pannel_size_h = document.getElementById("ps_h").value;
      let pannel_size_w = document.getElementById("ps_w").value;
      let pannel_size_d = document.getElementById("ps_d").value;

      let front_door_h = document.getElementById("fd_h").value;
      let front_door_w = document.getElementById("fd_w").value;
      let front_door_q = document.getElementById("fd_q").value;
      let front_door_ss = document.getElementById("fd_sheet").value;
      let front_door_sf = $(".fd_cal").val();
      let front_door_ws = $(".fd_WBS").val();

      let s_front_door_h = document.getElementById("s_fd_h").value;
      let s_front_door_w = document.getElementById("s_fd_w").value;
      let s_front_door_q = document.getElementById("s_fd_q").value;
      let s_front_door_ss = document.getElementById("s_fd_sheet").value;
      let s_front_door_sf = $(".s_fd_cal").val();
      let s_front_door_ws = $(".s_fd_WBS").val();

      let t_front_door_h = document.getElementById("t_fd_h").value;
      let t_front_door_w = document.getElementById("t_fd_w").value;
      let t_front_door_q = document.getElementById("t_fd_q").value;
      let t_front_door_ss = document.getElementById("t_fd_sheet").value;
      let t_front_door_sf = $(".t_fd_cal").val();
      let t_front_door_ws = $(".t_fd_WBS").val();

      let f_front_door_h = document.getElementById("f_fd_h").value;
      let f_front_door_w = document.getElementById("f_fd_w").value;
      let f_front_door_q = document.getElementById("f_fd_q").value;
      let f_front_door_ss = document.getElementById("f_fd_sheet").value;
      let f_front_door_sf = $(".f_fd_cal").val();
      let f_front_door_ws = $(".f_fd_WBS").val();

      let fth_front_door_h = document.getElementById("fth_fd_h").value;
      let fth_front_door_w = document.getElementById("fth_fd_w").value;
      let fth_front_door_q = document.getElementById("fth_fd_q").value;
      let fth_front_door_ss = document.getElementById("fth_fd_sheet").value;
      let fth_front_door_sf = $(".fth_fd_cal").val();
      let fth_front_door_ws = $(".fth_fd_WBS").val();

      let six_front_door_h = document.getElementById("six_fd_h").value;
      let six_front_door_w = document.getElementById("six_fd_w").value;
      let six_front_door_q = document.getElementById("six_fd_q").value;
      let six_front_door_ss = document.getElementById("six_fd_sheet").value;
      let six_front_door_sf = $(".six_fd_cal").val();
      let six_front_door_ws = $(".six_fd_WBS").val();

      let sth_front_door_h = document.getElementById("sth_fd_h").value;
      let sth_front_door_w = document.getElementById("sth_fd_w").value;
      let sth_front_door_q = document.getElementById("sth_fd_q").value;
      let sth_front_door_ss = document.getElementById("sth_fd_sheet").value;
      let sth_front_door_sf = $(".sth_fd_cal").val();
      let sth_front_door_ws = $(".sth_fd_WBS").val();

      let e_front_door_h = document.getElementById("e_fd_h").value;
      let e_front_door_w = document.getElementById("e_fd_w").value;
      let e_front_door_q = document.getElementById("e_fd_q").value;
      let e_front_door_ss = document.getElementById("e_fd_sheet").value;
      let e_front_door_sf = $(".e_fd_cal").val();
      let e_front_door_ws = $(".e_fd_WBS").val();

      let n_front_door_h = document.getElementById("n_fd_h").value;
      let n_front_door_w = document.getElementById("n_fd_w").value;
      let n_front_door_q = document.getElementById("n_fd_q").value;
      let n_front_door_ss = document.getElementById("n_fd_sheet").value;
      let n_front_door_sf = $(".n_fd_cal").val();
      let n_front_door_ws = $(".n_fd_WBS").val();

      let back_door_h = document.getElementById("bd_h").value;
      let back_door_w = document.getElementById("bd_w").value;
      let back_door_q = document.getElementById("bd_q").value;
      let back_door_ss = document.getElementById("bd_sheet").value;
      let back_door_sf = $(".bd_cal").val();
      let back_door_ws = $(".bd_WBS").val();

      let s_back_door_h = document.getElementById("s_bd_h").value;
      let s_back_door_w = document.getElementById("s_bd_w").value;
      let s_back_door_q = document.getElementById("s_bd_q").value;
      let s_back_door_ss = document.getElementById("s_bd_sheet").value;
      let s_back_door_sf = $(".s_bd_cal").val();
      let s_back_door_ws = $(".s_bd_WBS").val();

      let t_back_door_h = document.getElementById("t_bd_h").value;
      let t_back_door_w = document.getElementById("t_bd_w").value;
      let t_back_door_q = document.getElementById("t_bd_q").value;
      let t_back_door_ss = document.getElementById("t_bd_sheet").value;
      let t_back_door_sf = $(".t_bd_cal").val();
      let t_back_door_ws = $(".t_bd_WBS").val();

      let f_back_door_h = document.getElementById("f_bd_h").value;
      let f_back_door_w = document.getElementById("f_bd_w").value;
      let f_back_door_q = document.getElementById("f_bd_q").value;
      let f_back_door_ss = document.getElementById("f_bd_sheet").value;
      let f_back_door_sf = $(".f_bd_cal").val();
      let f_back_door_ws = $(".f_bd_WBS").val();

      let fth_back_door_h = document.getElementById("fth_bd_h").value;
      let fth_back_door_w = document.getElementById("fth_bd_w").value;
      let fth_back_door_q = document.getElementById("fth_bd_q").value;
      let fth_back_door_ss = document.getElementById("fth_bd_sheet").value;
      let fth_back_door_sf = $(".fth_bd_cal").val();
      let fth_back_door_ws = $(".fth_bd_WBS").val();

      let six_back_door_h = document.getElementById("six_bd_h").value;
      let six_back_door_w = document.getElementById("six_bd_w").value;
      let six_back_door_q = document.getElementById("six_bd_q").value;
      let six_back_door_ss = document.getElementById("six_bd_sheet").value;
      let six_back_door_sf = $(".six_bd_cal").val();
      let six_back_door_ws = $(".six_bd_WBS").val();

      let sth_back_door_h = document.getElementById("sth_bd_h").value;
      let sth_back_door_w = document.getElementById("sth_bd_w").value;
      let sth_back_door_q = document.getElementById("sth_bd_q").value;
      let sth_back_door_ss = document.getElementById("sth_bd_sheet").value;
      let sth_back_door_sf = $(".sth_bd_cal").val();
      let sth_back_door_ws = $(".sth_bd_WBS").val();

      let e_back_door_h = document.getElementById("e_bd_h").value;
      let e_back_door_w = document.getElementById("e_bd_w").value;
      let e_back_door_q = document.getElementById("e_bd_q").value;
      let e_back_door_ss = document.getElementById("e_bd_sheet").value;
      let e_back_door_sf = $(".e_bd_cal").val();
      let e_back_door_ws = $(".e_bd_WBS").val();

      let n_back_door_h = document.getElementById("n_bd_h").value;
      let n_back_door_w = document.getElementById("n_bd_w").value;
      let n_back_door_q = document.getElementById("n_bd_q").value;
      let n_back_door_ss = document.getElementById("n_bd_sheet").value;
      let n_back_door_sf = $(".n_bd_cal").val();
      let n_back_door_ws = $(".n_bd_WBS").val();

      let side_door_RL_h = document.getElementById("sd_h").value;
      let side_door_RL_w = document.getElementById("sd_w").value;
      let side_door_RL_q = document.getElementById("sd_q").value;
      let side_door_RL_ss = document.getElementById("sd_sheet").value;
      let side_door_RL_sf = $(".sd_cal").val();
      let side_door_RL_ws = $(".sd_WBS").val();

      let VS_piece_FB_L = document.getElementById("vs_h").value;
      let VS_piece_FB_w = document.getElementById("vs_w").value;
      let VS_piece_FB_q = document.getElementById("vs_q").value;
      let VS_piece_FB_ss = document.getElementById("vs_sheet").value;
      let VS_piece_FB_sf = $(".vs_cal").val();
      let VS_piece_FB_ws = $(".vs_WBS").val();

      let HS_piece_FB_L = document.getElementById("hs_h").value;
      let HS_piece_FB_w = document.getElementById("hs_w").value;
      let HS_piece_FB_q = document.getElementById("hs_q").value;
      let HS_piece_FB_ss = document.getElementById("hs_sheet").value;
      let HS_piece_FB_sf = $(".hs_cal").val();
      let HS_piece_FB_ws = $(".vs_WBS").val();

      let HS_piece_RL_h = document.getElementById("hsp_h").value;
      let HS_piece_RL_w = document.getElementById("hsp_w").value;
      let HS_piece_RL_q = document.getElementById("hsp_q").value;
      let HS_piece_RL_ss = document.getElementById("hsp_sheet").value;
      let HS_piece_RL_sf = $(".hsp_cal").val();
      let HS_piece_RL_ws = $(".hsp_cal").val();

      let componnent_plate_h = document.getElementById("cp_h").value;
      let componnent_plate_w = document.getElementById("cp_w").value;
      let componnent_plate_q = document.getElementById("cp_q").value;
      let componnent_plate_ss = document.getElementById("cp_sheet").value;
      let componnent_plate_sf = $(".cp_cal").val();
      let componnent_plate_ws = $(".cp_WBS").val();

      let s_componnent_plate_h = document.getElementById("s_cp_h").value;
      let s_componnent_plate_w = document.getElementById("s_cp_w").value;
      let s_componnent_plate_q = document.getElementById("s_cp_q").value;
      let s_componnent_plate_ss = document.getElementById("s_cp_sheet").value;
      let s_componnent_plate_sf = $(".s_cp_cal").val();
      let s_componnent_plate_ws = $(".s_cp_WBS").val();

      let t_componnent_plate_h = document.getElementById("t_cp_h").value;
      let t_componnent_plate_w = document.getElementById("t_cp_w").value;
      let t_componnent_plate_q = document.getElementById("t_cp_q").value;
      let t_componnent_plate_ss = document.getElementById("t_cp_sheet").value;
      let t_componnent_plate_sf = $(".t_cp_cal").val();
      let t_componnent_plate_ws = $(".t_cp_WBS").val();

      let f_componnent_plate_h = document.getElementById("f_cp_h").value;
      let f_componnent_plate_w = document.getElementById("f_cp_w").value;
      let f_componnent_plate_q = document.getElementById("f_cp_q").value;
      let f_componnent_plate_ss = document.getElementById("f_cp_sheet").value;
      let f_componnent_plate_sf = $(".f_cp_cal").val();
      let f_componnent_plate_ws = $(".f_cp_WBS").val();

      let fth_componnent_plate_h = document.getElementById("fth_cp_h").value;
      let fth_componnent_plate_w = document.getElementById("fth_cp_w").value;
      let fth_componnent_plate_q = document.getElementById("fth_cp_q").value;
      let fth_componnent_plate_ss = document.getElementById("fth_cp_sheet").value;
      let fth_componnent_plate_sf = $(".fth_cp_cal").val();
      let fth_componnent_plate_ws = $(".fth_cp_WBS").val();

      let six_componnent_plate_h = document.getElementById("six_cp_h").value;
      let six_componnent_plate_w = document.getElementById("six_cp_w").value;
      let six_componnent_plate_q = document.getElementById("six_cp_q").value;
      let six_componnent_plate_ss = document.getElementById("six_cp_sheet").value;
      let six_componnent_plate_sf = $(".six_cp_cal").val();
      let six_componnent_plate_ws = $(".six_cp_WBS").val();

      let sth_componnent_plate_h = document.getElementById("sth_cp_h").value;
      let sth_componnent_plate_w = document.getElementById("sth_cp_w").value;
      let sth_componnent_plate_q = document.getElementById("sth_cp_q").value;
      let sth_componnent_plate_ss = document.getElementById("sth_cp_sheet").value;
      let sth_componnent_plate_sf = $(".sth_cp_cal").val();
      let sth_componnent_plate_ws = $(".sth_cp_WBS").val();

      let e_componnent_plate_h = document.getElementById("e_cp_h").value;
      let e_componnent_plate_w = document.getElementById("e_cp_w").value;
      let e_componnent_plate_q = document.getElementById("e_cp_q").value;
      let e_componnent_plate_ss = document.getElementById("e_cp_sheet").value;
      let e_componnent_plate_sf = $(".e_cp_cal").val();
      let e_componnent_plate_ws = $(".e_cp_WBS").val();

      let n_componnent_plate_h = document.getElementById("n_cp_h").value;
      let n_componnent_plate_w = document.getElementById("n_cp_w").value;
      let n_componnent_plate_q = document.getElementById("n_cp_q").value;
      let n_componnent_plate_ss = document.getElementById("n_cp_sheet").value;
      let n_componnent_plate_sf = $(".n_cp_cal").val();
      let n_componnent_plate_ws = $(".n_cp_WBS").val();

      let pallet_L = document.getElementById("p_h").value;
      let pallet_w = document.getElementById("p_w").value;
      let pallet_q = document.getElementById("p_q").value;
      let pallet_ss = document.getElementById("p_sheet").value;
      let pallet_sf = $(".p_cal").val();
      let pallet_ws = $(".p_WBS").val();

      let s_pallet_L = document.getElementById("p_h").value;
      let s_pallet_w = document.getElementById("p_w").value;
      let s_pallet_q = document.getElementById("p_q").value;
      let s_pallet_ss = document.getElementById("p_sheet").value;
      let s_pallet_sf = $(".p_cal").val();
      let s_pallet_ws = $(".p_WBS").val();

      let FB_base_L = document.getElementById("front_back_h").value;
      let FB_base_w = document.getElementById("front_back_w").value;
      let FB_base_q = document.getElementById("front_back_q").value;
      let FB_base_ss = document.getElementById("front_back_sheet").value;
      let FB_base_sf = $(".front_back_cal").val();
      let FB_base_ws = $(".front_back_WBS").val();

      let RL_base_L = document.getElementById("right_left_h").value;
      let RL_base_w = document.getElementById("right_left_w").value;
      let RL_base_q = document.getElementById("right_left_q").value;
      let RL_base_ss = document.getElementById("right_left_sheet").value;
      let RL_base_sf = $(".right_left_cal").val();
      let RL_base_ws = $(".right_left_WBS").val();

      let VI_door_U_L = document.getElementById("vertical_inside_h").value;
      let VI_door_U_w = document.getElementById("vertical_inside_w").value;
      let VI_door_U_q = document.getElementById("vertical_inside_q").value;
      let VI_door_U_ss = document.getElementById("vertical_inside_sheet").value;
      let VI_door_U_sf = $(".vertical_inside_cal").val();
      let VI_door_U_ws = $(".vertical_inside_WBS").val();

      let HI_door_u_L = document.getElementById("horizonal_inside_h").value;
      let HI_door_u_w = document.getElementById("horizonal_inside_w").value;
      let HI_door_u_q = document.getElementById("horizonal_inside_q").value;
      let HI_door_u_ss = document.getElementById(
        "horizonal_inside_sheet"
      ).value;
      let HI_door_u_sf = $(".horizonal_inside_cal").val();
      let HI_door_u_ws = $(".horizonal_inside_WBS").val();

      let VI_L_type_L = document.getElementById(
        "Vertical_inside_piece_h"
      ).value;
      let VI_L_type_w = document.getElementById(
        "Vertical_inside_piece_w"
      ).value;
      let VI_L_type_q = document.getElementById(
        "Vertical_inside_piece_q"
      ).value;
      let VI_L_type_ss = document.getElementById(
        "Vertical_inside_piece_sheet"
      ).value;
      let VI_L_type_sf = $(".Vertical_inside_piece_cal").val();
      let VI_L_type_ws = $(".Vertical_inside_piece_WBS").val();

      let HI_U_type_L = document.getElementById(
        "horizental_inside_piece_U_h"
      ).value;
      let HI_U_type_w = document.getElementById(
        "horizental_inside_piece_U_w"
      ).value;
      let HI_U_type_q = document.getElementById(
        "horizental_inside_piece_U_q"
      ).value;
      let HI_U_type_ss = document.getElementById(
        "horizental_inside_piece_U_sheet"
      ).value;
      let HI_U_type_sf = $(".horizental_inside_piece_U_cal").val();
      let HI_U_type_ws = $(".horizental_inside_piece_U_WBS").val();

      let top_L = document.getElementById("top_h").value;
      let top_w = document.getElementById("top_w").value;
      let top_q = document.getElementById("top_q").value;
      let top_ss = document.getElementById("top_sheet").value;
      let top_sf = $(".top_cal").val();
      let top_ws = $(".top_WBS").val();

      let bottom_L = document.getElementById("bottom_h").value;
      let bottom_w = document.getElementById("bottom_w").value;
      let bottom_q = document.getElementById("bottom_q").value;
      let bottom_ss = document.getElementById("bottom_sheet").value;
      let bottom_sf = $(".bottom_cal").val();
      let bottom_ws = $(".bottom_WBS").val();

      let protection_sheet_h = document.getElementById("p_s_h").value;
      let protection_sheet_w = document.getElementById("p_s_w").value;
      let protection_sheet_q = document.getElementById("p_s_q").value;
      let protection_sheet_ss = document.getElementById("p_s_sheet").value;
      let protection_sheet_sf = $(".p_s_cal").val();
      let protection_sheet_ws = $(".p_s_WBS").val();

      let s_protection_sheet_h = document.getElementById("p_s_h").value;
      let s_protection_sheet_w = document.getElementById("p_s_w").value;
      let s_protection_sheet_q = document.getElementById("p_s_q").value;
      let s_protection_sheet_ss = document.getElementById("p_s_sheet").value;
      let s_protection_sheet_sf = $(".p_s_cal").val();
      let s_protection_sheet_ws = $(".p_s_WBS").val();

      let miscellaneous_h = document.getElementById("miscellaneous_h").value;
      let miscellaneous_w = document.getElementById("miscellaneous_w").value;
      let miscellaneous_q = document.getElementById("miscellaneous_q").value;
      let miscellaneous_ss = document.getElementById(
        "miscellaneous_sheet"
      ).value;
      let miscellaneous_sf = $(".miscellaneous_cal").val();
      let miscellaneous_ws = $(".miscellaneous_WBS").val();

      let sheet_type = document.getElementById("sheet_type").value;
      let matal_s_price = document.getElementById("matal_s_price").value;
      let stainless_s_price =
        document.getElementById("stainless_s_price").value;
      let galvanized_s_price =
        document.getElementById("galvanized_s_price").value;

      let pl_mf = document.getElementById("pl_mf").value;
      let pl_model = document.getElementById("pl_model").value;
      let pl_qty = document.getElementById("pl_qty").value;
      let pl_cost = $(".pl_cost").val();

      let h_mf = document.getElementById("h_mf").value;
      let h_model = document.getElementById("h_model").value;
      let h_qty = document.getElementById("h_qty").value;
      let h_cost = $(".h_cost").val();

      let as_qty = document.getElementById("as_qty").value;
      let as_cost = $(".as_cost").val();

      let gk_qty = document.getElementById("gk_qty").value;
      let gk_cost = $(".gk_cost").val();

      let i_qty = document.getElementById("i_qty").value;
      let i_cost = $(".i_cost").val();

      let cd_mf = document.getElementById("cd_mf").value;
      let cd_model = document.getElementById("cd_model").value;
      let cd_qty = document.getElementById("cd_qty").value;
      let cd_cost = $(".cd_cost").val();

      let pc_mf = document.getElementById("pc_mf").value;
      let pc_model = document.getElementById("pc_model").value;
      let pc_cost = $(".pc_cost").val();

      let first_foot_size = document.getElementById("first_foot_size").value;
      let first_sleeve_cost =
        document.getElementById("first_sleeve_cost").value;
      let first_factor = document.getElementById("first_factor").value;

      let second_foot_size = document.getElementById("second_foot_size").value;
      let second_sleeve_cost =
        document.getElementById("second_sleeve_cost").value;
      let second_factor = document.getElementById("second_factor").value;

      let third_foot_size = document.getElementById("third_foot_size").value;
      let third_sleeve_cost =
        document.getElementById("third_sleeve_cost").value;
      let third_factor = document.getElementById("third_factor").value;

      let fourth_foot_size = document.getElementById("fourth_foot_size").value;
      let fourth_sleeve_cost =
        document.getElementById("fourth_sleeve_cost").value;
      let fourth_factor = document.getElementById("fourth_factor").value;

      let fifth_foot_size = document.getElementById("fifth_foot_size").value;
      let fifth_sleeve_cost =
        document.getElementById("fifth_sleeve_cost").value;
      let fifth_factor = document.getElementById("fifth_factor").value;

      let sixth_foot_size = document.getElementById("sixth_foot_size").value;
      let sixth_sleeve_cost =
        document.getElementById("sixth_sleeve_cost").value;
      let sixth_factor = document.getElementById("sixth_factor").value;

      let t_5_foot_size = $(".20_5_foot_size").val();
      let t_5_sleeve_cost = $(".20_5_sleeve_cost").val();
      let t_5_factor = $(".20_5_factor").val();
      let t_5_sleeve_total_cost = $(".20_5_sleeve_total_cost").val();
      let t_5_bbr_weight = $(".20_5_bbr_weight").val();

      let tf_5_foot_size = $(".25_5_foot_size").val();
      let tf_5_sleeve_cost = $(".25_5_sleeve_cost").val();
      let tf_5_factor = $(".25_5_factor").val();
      let tf_5_sleeve_total_cost = $(".25_5_sleeve_total_cost").val();
      let tf_5_bbr_weight = $(".25_5_bbr_weight").val();

      let tf_10_foot_size = $(".25_10_foot_size").val();
      let tf_10_sleeve_cost = $(".25_10_sleeve_cost").val();
      let tf_10_factor = $(".25_10_factor").val();
      let tf_10_sleeve_total_cost = $(".25_10_sleeve_total_cost").val();
      let tf_10_bbr_weight = $(".25_10_bbr_weight").val();

      let ty_5_foot_size = $(".30_5_foot_size").val();
      let ty_5_sleeve_cost = $(".30_5_sleeve_cost").val();
      let ty_5_factor = $(".30_5_factor").val();
      let ty_5_sleeve_total_cost = $(".30_5_sleeve_total_cost").val();
      let ty_5_bbr_weight = $(".30_5_bbr_weight").val();

      let ty_10_foot_size = $(".30_10_foot_size").val();
      let ty_10_sleeve_cost = $(".30_10_sleeve_cost").val();
      let ty_10_factor = $(".30_10_factor").val();
      let ty_10_sleeve_total_cost = $(".30_10_sleeve_total_cost").val();
      let ty_10_bbr_weight = $(".30_10_bbr_weight").val();

      let f_5_foot_size = $(".40_5_foot_size").val();
      let f_5_sleeve_cost = $(".40_5_sleeve_cost").val();
      let f_5_factor = $(".40_5_factor").val();
      let f_5_sleeve_total_cost = $(".40_5_sleeve_total_cost").val();
      let f_5_bbr_weight = $(".40_5_bbr_weight").val();

      let f_10_foot_size = $(".40_10_foot_size").val();
      let f_10_sleeve_cost = $(".40_10_sleeve_cost").val();
      let f_10_factor = $(".40_10_factor").val();
      let f_10_sleeve_total_cost = $(".40_10_sleeve_total_cost").val();
      let f_10_bbr_weight = $(".40_10_bbr_weight").val();

      let fy_5_foot_size = $(".50_5_foot_size").val();
      let fy_5_sleeve_cost = $(".50_5_sleeve_cost").val();
      let fy_5_factor = $(".50_5_factor").val();
      let fy_5_sleeve_total_cost = $(".50_5_sleeve_total_cost").val();
      let fy_5_bbr_weight = $(".50_5_bbr_weight").val();

      let fy_10_foot_size = $(".50_10_foot_size").val();
      let fy_10_sleeve_cost = $(".50_10_sleeve_cost").val();
      let fy_10_factor = $(".50_10_factor").val();
      let fy_10_sleeve_total_cost = $(".50_10_sleeve_total_cost").val();
      let fy_10_bbr_weight = $(".50_10_bbr_weight").val();

      let s_5_foot_size = $(".60_5_foot_size").val();
      let s_5_sleeve_cost = $(".60_5_sleeve_cost").val();
      let s_5_factor = $(".60_5_factor").val();
      let s_5_sleeve_total_cost = $(".60_5_sleeve_total_cost").val();
      let s_5_bbr_weight = $(".60_5_bbr_weight").val();

      let s_10_foot_size = $(".60_10_foot_size").val();
      let s_10_sleeve_cost = $(".60_10_sleeve_cost").val();
      let s_10_factor = $(".60_10_factor").val();
      let s_10_sleeve_total_cost = $(".60_10_sleeve_total_cost").val();
      let s_10_bbr_weight = $(".60_10_bbr_weight").val();

      let e_5_foot_size = $(".80_5_foot_size").val();
      let e_5_sleeve_cost = $(".80_5_sleeve_cost").val();
      let e_5_factor = $(".80_5_factor").val();
      let e_5_sleeve_total_cost = $(".80_5_sleeve_total_cost").val();
      let e_5_bbr_weight = $(".80_5_bbr_weight").val();

      let e_10_foot_size = $(".80_10_foot_size").val();
      let e_10_sleeve_cost = $(".80_10_sleeve_cost").val();
      let e_10_factor = $(".80_10_factor").val();
      let e_10_sleeve_total_cost = $(".80_10_sleeve_total_cost").val();
      let e_10_bbr_weight = $(".80_10_bbr_weight").val();

      let h_5_foot_size = $(".100_5_foot_size").val();
      let h_5_sleeve_cost = $(".100_5_sleeve_cost").val();
      let h_5_factor = $(".100_5_factor").val();
      let h_5_sleeve_total_cost = $(".100_5_sleeve_total_cost").val();
      let h_5_bbr_weight = $(".100_5_bbr_weight").val();

      let h_10_foot_size = $(".100_10_foot_size").val();
      let h_10_sleeve_cost = $(".100_10_sleeve_cost").val();
      let h_10_factor = $(".100_10_factor").val();
      let h_10_sleeve_total_cost = $(".100_10_sleeve_total_cost").val();
      let h_10_bbr_weight = $(".100_10_bbr_weight").val();

      let ot_5_foot_size = $(".120_5_foot_size").val();
      let ot_5_sleeve_cost = $(".120_5_sleeve_cost").val();
      let ot_5_factor = $(".120_5_factor").val();
      let ot_5_sleeve_total_cost = $(".120_5_sleeve_total_cost").val();
      let ot_5_bbr_weight = $(".120_5_bbr_weight").val();

      let ot_10_foot_size = $(".120_10_foot_size").val();
      let ot_10_sleeve_cost = $(".120_10_sleeve_cost").val();
      let ot_10_factor = $(".120_10_factor").val();
      let ot_10_sleeve_total_cost = $(".120_10_sleeve_total_cost").val();
      let ot_10_bbr_weight = $(".120_10_bbr_weight").val();

      let of_10_foot_size = $(".150_10_foot_size").val();
      let of_10_sleeve_cost = $(".150_10_sleeve_cost").val();
      let of_10_factor = $(".150_10_factor").val();
      let of_10_sleeve_total_cost = $(".150_10_sleeve_total_cost").val();
      let of_10_bbr_weight = $(".150_10_bbr_weight").val();

      $.ajax({
        url: "cost/total_cost.php",
        method: "POST",
        data: {
          sheet_type: sheet_type,

          pannel_size_h: pannel_size_h,
          pannel_size_w: pannel_size_w,
          pannel_size_d: pannel_size_d,

          front_door_h: front_door_h,
          front_door_w: front_door_w,
          front_door_q: front_door_q,
          front_door_ss: front_door_ss,
          front_door_sf: front_door_sf,
          front_door_ws: front_door_ws,

          s_front_door_h: s_front_door_h,
          s_front_door_w: s_front_door_w,
          s_front_door_q: s_front_door_q,
          s_front_door_ss: s_front_door_ss,
          s_front_door_sf: s_front_door_sf,
          s_front_door_ws: s_front_door_ws,

          t_front_door_h: t_front_door_h,
          t_front_door_w: t_front_door_w,
          t_front_door_q: t_front_door_q,
          t_front_door_ss: t_front_door_ss,
          t_front_door_sf: t_front_door_sf,
          t_front_door_ws: t_front_door_ws,

          f_front_door_h: f_front_door_h,
          f_front_door_w: f_front_door_w,
          f_front_door_q: f_front_door_q,
          f_front_door_ss: f_front_door_ss,
          f_front_door_sf: f_front_door_sf,
          f_front_door_ws: f_front_door_ws,

          fth_front_door_h: fth_front_door_h,
          fth_front_door_w: fth_front_door_w,
          fth_front_door_q: fth_front_door_q,
          fth_front_door_ss: fth_front_door_ss,
          fth_front_door_sf: fth_front_door_sf,
          fth_front_door_ws: fth_front_door_ws,

          six_front_door_h: six_front_door_h,
          six_front_door_w: six_front_door_w,
          six_front_door_q: six_front_door_q,
          six_front_door_ss: six_front_door_ss,
          six_front_door_sf: six_front_door_sf,
          six_front_door_ws: six_front_door_ws,

          sth_front_door_h: sth_front_door_h,
          sth_front_door_w: sth_front_door_w,
          sth_front_door_q: sth_front_door_q,
          sth_front_door_ss: sth_front_door_ss,
          sth_front_door_sf: sth_front_door_sf,
          sth_front_door_ws: sth_front_door_ws,

          e_front_door_h: e_front_door_h,
          e_front_door_w: e_front_door_w,
          e_front_door_q: e_front_door_q,
          e_front_door_ss: e_front_door_ss,
          e_front_door_sf: e_front_door_sf,
          e_front_door_ws: e_front_door_ws,

          n_front_door_h: n_front_door_h,
          n_front_door_w: n_front_door_w,
          n_front_door_q: n_front_door_q,
          n_front_door_ss: n_front_door_ss,
          n_front_door_sf: n_front_door_sf,
          n_front_door_ws: n_front_door_ws,

          back_door_h: back_door_h,
          back_door_w: back_door_w,
          back_door_q: back_door_q,
          back_door_ss: back_door_ss,
          back_door_sf: back_door_sf,
          back_door_ws: back_door_ws,

          s_back_door_h: s_back_door_h,
          s_back_door_w: s_back_door_w,
          s_back_door_q: s_back_door_q,
          s_back_door_ss: s_back_door_ss,
          s_back_door_sf: s_back_door_sf,
          s_back_door_ws: s_back_door_ws,

          t_back_door_h: t_back_door_h,
          t_back_door_w: t_back_door_w,
          t_back_door_q: t_back_door_q,
          t_back_door_ss: t_back_door_ss,
          t_back_door_sf: t_back_door_sf,
          t_back_door_ws: t_back_door_ws,

          f_back_door_h: f_back_door_h,
          f_back_door_w: f_back_door_w,
          f_back_door_q: f_back_door_q,
          f_back_door_ss: f_back_door_ss,
          f_back_door_sf: f_back_door_sf,
          f_back_door_ws: f_back_door_ws,

          fth_back_door_h: fth_back_door_h,
          fth_back_door_w: fth_back_door_w,
          fth_back_door_q: fth_back_door_q,
          fth_back_door_ss: fth_back_door_ss,
          fth_back_door_sf: fth_back_door_sf,
          fth_back_door_ws: fth_back_door_ws,

          six_back_door_h: six_back_door_h,
          six_back_door_w: six_back_door_w,
          six_back_door_q: six_back_door_q,
          six_back_door_ss: six_back_door_ss,
          six_back_door_sf: six_back_door_sf,
          six_back_door_ws: six_back_door_ws,

          sth_back_door_h: sth_back_door_h,
          sth_back_door_w: sth_back_door_w,
          sth_back_door_q: sth_back_door_q,
          sth_back_door_ss: sth_back_door_ss,
          sth_back_door_sf: sth_back_door_sf,
          sth_back_door_ws: sth_back_door_ws,

          e_back_door_h: e_back_door_h,
          e_back_door_w: e_back_door_w,
          e_back_door_q: e_back_door_q,
          e_back_door_ss: e_back_door_ss,
          e_back_door_sf: e_back_door_sf,
          e_back_door_ws: e_back_door_ws,

          n_back_door_h: n_back_door_h,
          n_back_door_w: n_back_door_w,
          n_back_door_q: n_back_door_q,
          n_back_door_ss: n_back_door_ss,
          n_back_door_sf: n_back_door_sf,
          n_back_door_ws: n_back_door_ws,

          side_door_RL_h: side_door_RL_h,
          side_door_RL_w: side_door_RL_w,
          side_door_RL_q: side_door_RL_q,
          side_door_RL_ss: side_door_RL_ss,
          side_door_RL_sf: side_door_RL_sf,
          side_door_RL_ws: side_door_RL_ws,

          VS_piece_FB_L: VS_piece_FB_L,
          VS_piece_FB_w: VS_piece_FB_w,
          VS_piece_FB_q: VS_piece_FB_q,
          VS_piece_FB_ss: VS_piece_FB_ss,
          VS_piece_FB_sf: VS_piece_FB_sf,
          VS_piece_FB_ws: VS_piece_FB_ws,

          HS_piece_FB_L: HS_piece_FB_L,
          HS_piece_FB_w: HS_piece_FB_w,
          HS_piece_FB_q: HS_piece_FB_q,
          HS_piece_FB_ss: HS_piece_FB_ss,
          HS_piece_FB_sf: HS_piece_FB_sf,
          HS_piece_FB_ws: HS_piece_FB_ws,

          HS_piece_RL_h: HS_piece_RL_h,
          HS_piece_RL_w: HS_piece_RL_w,
          HS_piece_RL_q: HS_piece_RL_q,
          HS_piece_RL_ss: HS_piece_RL_ss,
          HS_piece_RL_sf: HS_piece_RL_sf,
          HS_piece_RL_ws: HS_piece_RL_ws,

          componnent_plate_h: componnent_plate_h,
          componnent_plate_w: componnent_plate_w,
          componnent_plate_q: componnent_plate_q,
          componnent_plate_ss: componnent_plate_ss,
          componnent_plate_sf: componnent_plate_sf,
          componnent_plate_ws: componnent_plate_ws,

          s_componnent_plate_h: s_componnent_plate_h,
          s_componnent_plate_w: s_componnent_plate_w,
          s_componnent_plate_q: s_componnent_plate_q,
          s_componnent_plate_ss: s_componnent_plate_ss,
          s_componnent_plate_sf: s_componnent_plate_sf,
          s_componnent_plate_ws: s_componnent_plate_ws,

          t_componnent_plate_h: t_componnent_plate_h,
          t_componnent_plate_w: t_componnent_plate_w,
          t_componnent_plate_q: t_componnent_plate_q,
          t_componnent_plate_ss: t_componnent_plate_ss,
          t_componnent_plate_sf: t_componnent_plate_sf,
          t_componnent_plate_ws: t_componnent_plate_ws,

          f_componnent_plate_h: f_componnent_plate_h,
          f_componnent_plate_w: f_componnent_plate_w,
          f_componnent_plate_q: f_componnent_plate_q,
          f_componnent_plate_ss: f_componnent_plate_ss,
          f_componnent_plate_sf: f_componnent_plate_sf,
          f_componnent_plate_ws: f_componnent_plate_ws,

          fth_componnent_plate_h: fth_componnent_plate_h,
          fth_componnent_plate_w: fth_componnent_plate_w,
          fth_componnent_plate_q: fth_componnent_plate_q,
          fth_componnent_plate_ss: fth_componnent_plate_ss,
          fth_componnent_plate_sf: fth_componnent_plate_sf,
          fth_componnent_plate_ws: fth_componnent_plate_ws,

          six_componnent_plate_h: six_componnent_plate_h,
          six_componnent_plate_w: six_componnent_plate_w,
          six_componnent_plate_q: six_componnent_plate_q,
          six_componnent_plate_ss: six_componnent_plate_ss,
          six_componnent_plate_sf: six_componnent_plate_sf,
          six_componnent_plate_ws: six_componnent_plate_ws,

          sth_componnent_plate_h: sth_componnent_plate_h,
          sth_componnent_plate_w: sth_componnent_plate_w,
          sth_componnent_plate_q: sth_componnent_plate_q,
          sth_componnent_plate_ss: sth_componnent_plate_ss,
          sth_componnent_plate_sf: sth_componnent_plate_sf,
          sth_componnent_plate_ws: sth_componnent_plate_ws,

          e_componnent_plate_h: e_componnent_plate_h,
          e_componnent_plate_w: e_componnent_plate_w,
          e_componnent_plate_q: e_componnent_plate_q,
          e_componnent_plate_ss: e_componnent_plate_ss,
          e_componnent_plate_sf: e_componnent_plate_sf,
          e_componnent_plate_ws: e_componnent_plate_ws,

          n_componnent_plate_h: n_componnent_plate_h,
          n_componnent_plate_w: n_componnent_plate_w,
          n_componnent_plate_q: n_componnent_plate_q,
          n_componnent_plate_ss: n_componnent_plate_ss,
          n_componnent_plate_sf: n_componnent_plate_sf,
          n_componnent_plate_ws: n_componnent_plate_ws,

          pallet_L: pallet_L,
          pallet_w: pallet_w,
          pallet_q: pallet_q,
          pallet_ss: pallet_ss,
          pallet_sf: pallet_sf,
          pallet_ws: pallet_ws,

          s_pallet_L: s_pallet_L,
          s_pallet_w: s_pallet_w,
          s_pallet_q: s_pallet_q,
          s_pallet_ss: s_pallet_ss,
          s_pallet_sf: s_pallet_sf,
          s_pallet_ws: s_pallet_ws,

          FB_base_L: FB_base_L,
          FB_base_w: FB_base_w,
          FB_base_q: FB_base_q,
          FB_base_ss: FB_base_ss,
          FB_base_sf: FB_base_sf,
          FB_base_ws: FB_base_ws,

          RL_base_L: RL_base_L,
          RL_base_w: RL_base_w,
          RL_base_q: RL_base_q,
          RL_base_ss: RL_base_ss,
          RL_base_sf: RL_base_sf,
          RL_base_ws: RL_base_ws,

          VI_door_U_L: VI_door_U_L,
          VI_door_U_w: VI_door_U_w,
          VI_door_U_q: VI_door_U_q,
          VI_door_U_ss: VI_door_U_ss,
          VI_door_U_sf: VI_door_U_sf,
          VI_door_U_ws: VI_door_U_ws,

          HI_door_u_L: HI_door_u_L,
          HI_door_u_w: HI_door_u_w,
          HI_door_u_q: HI_door_u_q,
          HI_door_u_ss: HI_door_u_ss,
          HI_door_u_sf: HI_door_u_sf,
          HI_door_u_ws: HI_door_u_ws,

          VI_L_type_L: VI_L_type_L,
          VI_L_type_w: VI_L_type_w,
          VI_L_type_q: VI_L_type_q,
          VI_L_type_ss: VI_L_type_ss,
          VI_L_type_sf: VI_L_type_sf,
          VI_L_type_ws: VI_L_type_ws,

          HI_U_type_L: HI_U_type_L,
          HI_U_type_w: HI_U_type_w,
          HI_U_type_q: HI_U_type_q,
          HI_U_type_ss: HI_U_type_ss,
          HI_U_type_sf: HI_U_type_sf,
          HI_U_type_ws: HI_U_type_ws,

          top_L: top_L,
          top_w: top_w,
          top_q: top_q,
          top_ss: top_ss,
          top_sf: top_sf,
          top_ws: top_ws,

          bottom_L: bottom_L,
          bottom_w: bottom_w,
          bottom_q: bottom_q,
          bottom_ss: bottom_ss,
          bottom_sf: bottom_sf,
          bottom_ws: bottom_ws,

          protection_sheet_h: protection_sheet_h,
          protection_sheet_w: protection_sheet_w,
          protection_sheet_q: protection_sheet_q,
          protection_sheet_ss: protection_sheet_ss,
          protection_sheet_sf: protection_sheet_sf,
          protection_sheet_ws: protection_sheet_ws,

          s_protection_sheet_h: s_protection_sheet_h,
          s_protection_sheet_w: s_protection_sheet_w,
          s_protection_sheet_q: s_protection_sheet_q,
          s_protection_sheet_ss: s_protection_sheet_ss,
          s_protection_sheet_sf: s_protection_sheet_sf,
          s_protection_sheet_ws: s_protection_sheet_ws,

          miscellaneous_h: miscellaneous_h,
          miscellaneous_w: miscellaneous_w,
          miscellaneous_q: miscellaneous_q,
          miscellaneous_ss: miscellaneous_ss,
          miscellaneous_sf: miscellaneous_sf,
          miscellaneous_ws: miscellaneous_ws,

          sheet_type: sheet_type,
          matal_s_price: matal_s_price,
          stainless_s_price: stainless_s_price,
          galvanized_s_price: galvanized_s_price,

          pl_mf: pl_mf,
          pl_model: pl_model,
          pl_qty: pl_qty,
          pl_cost: pl_cost,

          h_mf: h_mf,
          h_model: h_model,
          h_qty: h_qty,
          h_cost: h_cost,

          as_qty: as_qty,
          as_cost: as_cost,

          gk_qty: gk_qty,
          gk_cost: gk_cost,

          i_qty: i_qty,
          i_cost: i_cost,

          cd_mf: cd_mf,
          cd_model: cd_model,
          cd_qty: cd_qty,
          cd_cost: cd_cost,

          pc_mf: pc_mf,
          pc_model: pc_model,
          pc_cost: pc_cost,

          first_foot_size: first_foot_size,
          first_sleeve_cost: first_sleeve_cost,
          first_factor: first_factor,

          second_foot_size: second_foot_size,
          second_sleeve_cost: second_sleeve_cost,
          second_factor: second_factor,

          third_foot_size: third_foot_size,
          third_sleeve_cost: third_sleeve_cost,
          third_factor: third_factor,

          fourth_foot_size: fourth_foot_size,
          fourth_sleeve_cost: fourth_sleeve_cost,
          fourth_factor: fourth_factor,

          fifth_foot_size: fifth_foot_size,
          fifth_sleeve_cost: fifth_sleeve_cost,
          fifth_factor: fifth_factor,

          sixth_foot_size: sixth_foot_size,
          sixth_sleeve_cost: sixth_sleeve_cost,
          sixth_factor: sixth_factor,

          t_5_foot_size: t_5_foot_size,
          t_5_sleeve_cost: t_5_sleeve_cost,
          t_5_factor: t_5_factor,
          t_5_sleeve_total_cost: t_5_sleeve_total_cost,
          t_5_bbr_weight: t_5_bbr_weight,

          tf_5_foot_size: tf_5_foot_size,
          tf_5_sleeve_cost: tf_5_sleeve_cost,
          tf_5_factor: tf_5_factor,
          tf_5_sleeve_total_cost: tf_5_sleeve_total_cost,
          tf_5_bbr_weight: tf_5_bbr_weight,

          tf_10_foot_size: tf_10_foot_size,
          tf_10_sleeve_cost: tf_10_sleeve_cost,
          tf_10_factor: tf_10_factor,
          tf_10_sleeve_total_cost: tf_10_sleeve_total_cost,
          tf_10_bbr_weight: tf_10_bbr_weight,

          ty_5_foot_size: ty_5_foot_size,
          ty_5_sleeve_cost: ty_5_sleeve_cost,
          ty_5_factor: ty_5_factor,
          ty_5_sleeve_total_cost: ty_5_sleeve_total_cost,
          ty_5_bbr_weight: ty_5_bbr_weight,

          ty_10_foot_size: ty_10_foot_size,
          ty_10_sleeve_cost: ty_10_sleeve_cost,
          ty_10_factor: ty_10_factor,
          ty_10_sleeve_total_cost: ty_10_sleeve_total_cost,
          ty_10_bbr_weight: ty_10_bbr_weight,

          f_5_foot_size: f_5_foot_size,
          f_5_sleeve_cost: f_5_sleeve_cost,
          f_5_factor: f_5_factor,
          f_5_sleeve_total_cost: f_5_sleeve_total_cost,
          f_5_bbr_weight: f_5_bbr_weight,

          f_10_foot_size: f_10_foot_size,
          f_10_sleeve_cost: f_10_sleeve_cost,
          f_10_factor: f_10_factor,
          f_10_sleeve_total_cost: f_10_sleeve_total_cost,
          f_10_bbr_weight: f_10_bbr_weight,

          fy_5_foot_size: fy_5_foot_size,
          fy_5_sleeve_cost: fy_5_sleeve_cost,
          fy_5_factor: fy_5_factor,
          fy_5_sleeve_total_cost: fy_5_sleeve_total_cost,
          fy_5_bbr_weight: fy_5_bbr_weight,

          fy_10_foot_size: fy_10_foot_size,
          fy_10_sleeve_cost: fy_10_sleeve_cost,
          fy_10_factor: fy_10_factor,
          fy_10_sleeve_total_cost: fy_10_sleeve_total_cost,
          fy_10_bbr_weight: fy_10_bbr_weight,

          s_5_foot_size: s_5_foot_size,
          s_5_sleeve_cost: s_5_sleeve_cost,
          s_5_factor: s_5_factor,
          s_5_sleeve_total_cost: s_5_sleeve_total_cost,
          s_5_bbr_weight: s_5_bbr_weight,

          s_10_foot_size: s_10_foot_size,
          s_10_sleeve_cost: s_10_sleeve_cost,
          s_10_factor: s_10_factor,
          s_10_sleeve_total_cost: s_10_sleeve_total_cost,
          s_10_bbr_weight: s_10_bbr_weight,

          e_5_foot_size: e_5_foot_size,
          e_5_sleeve_cost: e_5_sleeve_cost,
          e_5_factor: e_5_factor,
          e_5_sleeve_total_cost: e_5_sleeve_total_cost,
          e_5_bbr_weight: e_5_bbr_weight,

          e_10_foot_size: e_10_foot_size,
          e_10_sleeve_cost: e_10_sleeve_cost,
          e_10_factor: e_10_factor,
          e_10_sleeve_total_cost: e_10_sleeve_total_cost,
          e_10_bbr_weight: e_10_bbr_weight,

          h_5_foot_size: h_5_foot_size,
          h_5_sleeve_cost: h_5_sleeve_cost,
          h_5_factor: h_5_factor,
          h_5_sleeve_total_cost: h_5_sleeve_total_cost,
          h_5_bbr_weight: h_5_bbr_weight,

          h_10_foot_size: h_10_foot_size,
          h_10_sleeve_cost: h_10_sleeve_cost,
          h_10_factor: h_10_factor,
          h_10_sleeve_total_cost: h_10_sleeve_total_cost,
          h_10_bbr_weight: h_10_bbr_weight,

          ot_5_foot_size: ot_5_foot_size,
          ot_5_sleeve_cost: ot_5_sleeve_cost,
          ot_5_factor: ot_5_factor,
          ot_5_sleeve_total_cost: ot_5_sleeve_total_cost,
          ot_5_bbr_weight: ot_5_bbr_weight,

          ot_10_foot_size: ot_10_foot_size,
          ot_10_sleeve_cost: ot_10_sleeve_cost,
          ot_10_factor: ot_10_factor,
          ot_10_sleeve_total_cost: ot_10_sleeve_total_cost,
          ot_10_bbr_weight: ot_10_bbr_weight,

          of_10_foot_size: of_10_foot_size,
          of_10_sleeve_cost: of_10_sleeve_cost,
          of_10_factor: of_10_factor,
          of_10_sleeve_total_cost: of_10_sleeve_total_cost,
          of_10_bbr_weight: of_10_bbr_weight,

          bus_bar_price: bus_bar_price,
          pannel_costing_id: pannel_costing_id,
        },
        success: function (data) {
          var respData = JSON.parse(data);
          $("#g_total_weight").val(respData.gauges_total_weight);
          $(".total_sf").val(respData.total_square_feet);
          $(".s_by_sf").val(respData.s_by_sf);
          $(".sheet_consume").val(Math.round(respData.sheet_consume));
          $(".mlt_gauge_pr").val(respData.mlt_gauge_pr);
          $(".twelve_SWG").val(respData.twelve_SWG);
          $(".f_SWG").val(respData.f_SWG);
          $(".sixtn_SWG").val(respData.sixtn_SWG);
          $(".atn_SMG").val(respData.atn_SMG);
          $(".twenty_SMG").val(respData.twenty_SMG);
          $(".mlt_gauge_pr").val(respData.mlt_gauge_pr);
          $(".bbr_total_weight").val(respData.bbr_total_weight);
          $(".bbr_total_cost").val(respData.bbr_total_cost);
          $(".tin_cost").val(respData.tin_cost);
          $(".total_sleeve_cost").val(respData.total_sleeve_cost);
        },
      });
    } else {
      Swal.fire("Please Enter Bus Bar Price First On Page 22");
    }
  } else {
    Swal.fire("Please Select Sheet Type First On Page 20");
  }
});

$(document).ready(function () {

  let sheet_type = document.getElementById("sheet_type").value;
  let matal_s_price = document.getElementById("matal_s_price").value;
  let stainless_s_price = document.getElementById("stainless_s_price").value;
  let galvanized_s_price = document.getElementById("galvanized_s_price").value;

  if(sheet_type != "" && sheet_type != "0"){
    if(matal_s_price != "" && matal_s_price != "0"){
      $("#Matal").show();
    }
   else if(stainless_s_price != "" && stainless_s_price != "0"){
    $("#Stainless").show();
   }
   else if(galvanized_s_price != "" && galvanized_s_price != "0"){
 $("#Galvanized").show();
   }
  };

  $("#sheet_type").on("change", function () {
    $(this)
      .find("option:selected")
      .each(function () {
        if ($(this).attr("value") == "0") {
          $("#Matal").show();
          $("#Galvanized").hide();
          $("#Stainless").hide();
        } else if ($(this).attr("value") == "1") {
          $("#Stainless").show();
          $("#Matal").hide();
          $("#Galvanized").hide();
        } else {
          $("#Galvanized").show();
          $("#Matal").hide();
          $("#Stainless").hide();
        }
      });
  });

  let pl_mf = document.getElementById("pl_mf").value;
  if(pl_mf != "" && pl_mf != "0"){
    $("#pl_mf").show();
    $("#pl_qty").show();
  };
  $("#pl_model").on("change", function () {
    $("#pl_mf").show();
    $("#pl_qty").show();
  });

  let h_mf = document.getElementById("h_mf").value;
  if(h_mf != "" && h_mf != "0"){
    $("#h_mf").show();
    $("#h_qty").show();
  };
  $("#h_model").on("change", function () {
    $("#h_mf").show();
    $("#h_qty").show();
  });

  let cd_mf = document.getElementById("cd_mf").value;
  if(cd_mf != "" && cd_mf != "0"){
    $("#cd_mf").show();
    $("#cd_qty").show();
  };
  $("#cd_model").on("change", function () {
    $("#cd_mf").show();
    $("#cd_qty").show();
  });

  let pc_mf = document.getElementById("pc_mf").value;
  if(pc_mf != "" && pc_mf != "0"){
    $("#pc_mf").show();
  };
  $("#pc_model").on("change", function () {
    $("#pc_mf").show();
  });
});

// For calcuation of STATIONARAY COST pannel lock
$(document).on("click", "#pl_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let pl_mf = document.getElementById("pl_mf").value;
  let pl_model = document.getElementById("pl_model").value;
  let pl_qty = document.getElementById("pl_qty").value;

  if (pl_model != "" && pl_qty != "" && pl_mf != "") {
    let pl_cost = pl_mf * pl_qty;
    $(".pl_cost").val(pl_cost);

    $.ajax({
      url: "cost/stationary/pl.php",
      method: "POST",
      data: {
        pl_mf: pl_mf,
        pl_model: pl_model,
        pl_qty: pl_qty,
        pl_cost: pl_cost,
        pannel_costing_id: pannel_costing_id,
      }
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".pl_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/stationary/pl_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#pl_mf").val("");
        $("#pl_model").val("");
        $("#pl_qty").val("");
        $(".pl_cost").val("");
      },
    });
    $("#twenty_three").css("background-color", "rgb(187, 187, 187)");
});


// For calcuation of STATIONARAY COST Hingers
$(document).on("click", "#h_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let h_mf = document.getElementById("h_mf").value;
  let h_model = document.getElementById("h_model").value;
  let h_qty = document.getElementById("h_qty").value;

  if (h_model != "" && h_qty != "" && h_mf != "") {
    let h_cost = h_mf * h_qty;
    $(".h_cost").val(h_cost);

    $.ajax({
      url: "cost/stationary/h.php",
      method: "POST",
      data: {
        h_mf: h_mf,
        h_model: h_model,
        h_qty: h_qty,
        h_cost: h_cost,
        pannel_costing_id: pannel_costing_id,
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".h_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/stationary/h_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#h_mf").val("");
        $("#h_model").val("");
        $("#h_qty").val("");
        $(".h_cost").val("");
      },
    });
    $("#twenty_four").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of STATIONARAY COST acrylic sheet
$(document).on("click", "#as_cost", function () {
  event.preventDefault();
  
  let as_qty = document.getElementById("as_qty").value;
  let as_uc = document.getElementById("as_uc").value;
  let pannel_costing_id = $(".pannel_costing_id").val();

  if (as_qty != "") {
    as_cost = as_qty * as_uc;
    $(".as_cost").val(as_cost);
    $.ajax({
      url: "cost/stationary/as.php",
      method: "POST",
      data: {
        as_qty: as_qty,
        as_uc: as_uc,
        as_cost: as_cost,
        pannel_costing_id: pannel_costing_id,
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".as_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/stationary/as_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#as_qty").val("");
        $(".as_uc").val("");
        $(".as_cost").val("");
      },
    });
    $("#twenty_five").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of STATIONARAY COST GAS KIT
$(document).on("click", "#gk_cost", function () {
  event.preventDefault();
  let gk_qty = document.getElementById("gk_qty").value;
  let gk_uc = document.getElementById("gk_uc").value;
  let pannel_costing_id = $(".pannel_costing_id").val();

  if (gk_qty != "") {
    let gk_cost = gk_qty * gk_uc;
    $(".gk_cost").val(gk_cost);
    $.ajax({
      url: "cost/stationary/gk.php",
      method: "POST",
      data: {
        gk_qty: gk_qty,
        gk_uc:gk_uc,
        gk_cost: gk_cost,
        pannel_costing_id: pannel_costing_id,
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".gk_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/stationary/gk_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#gk_qty").val("");
        $("#gk_uc").val("");
        $(".gk_cost").val("");
      },
    });
    $("#twenty_six").css("background-color", "rgb(187, 187, 187)");
});


// For calcuation of STATIONARAY COST GAS KIT
$(document).on("click", "#i_cost", function () {
  event.preventDefault();
  let i_qty = document.getElementById("i_qty").value;
  let i_uc = document.getElementById("i_uc").value;
  let pannel_costing_id = $(".pannel_costing_id").val();

  if (i_qty != "") {
    let i_cost = i_qty * i_uc;
    $(".i_cost").val(i_cost);
    $.ajax({
      url: "cost/stationary/i.php",
      method: "POST",
      data: {
        i_qty: i_qty,
        i_uc:i_uc,
        i_cost: i_cost,
        pannel_costing_id: pannel_costing_id,
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".i_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/stationary/i_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#i_qty").val("");
        $(".i_cost").val("");
        $("#i_uc").val("");
      },
    });
    $("#twenty_seven").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of STATIONARAY COST pannel lock
$(document).on("click", "#cd_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let cd_mf = document.getElementById("cd_mf").value;
  let cd_model = document.getElementById("cd_model").value;
  let cd_qty = document.getElementById("cd_qty").value;

  if (cd_model != "" && cd_qty != "" && cd_mf != "") {
    let cd_cost = cd_mf * cd_qty;
    $(".cd_cost").val(cd_cost);
    $.ajax({
      url: "cost/stationary/cd.php",
      method: "POST",
      data: {
        cd_qty: cd_qty,
        cd_mf: cd_mf,
        cd_model: cd_model,
        cd_cost: cd_cost,
        pannel_costing_id: pannel_costing_id,
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".cd_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/stationary/cd_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#cd_mf").val("");
        $(".cd_model").val("");
        $("#cd_qty").val("");
        $(".cd_cost").val("");
      },
    });
    $("#twenty_eight").css("background-color", "rgb(187, 187, 187)");
});

// For calcuation of STATIONARAY COST pannel lock
$(document).on("click", "#pc_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let pc_mf = document.getElementById("pc_mf").value;
  let pc_model = document.getElementById("pc_model").value;
  let pannel_size_h = document.getElementById("ps_h").value;
  let pannel_size_w = document.getElementById("ps_w").value;

  if (pc_model != "" && pc_mf != "") {
    let pc_cost = ((pannel_size_h * pannel_size_w) / 645.16 / 144) * pc_mf;
    $(".pc_cost").val(pc_cost);
    $.ajax({
      url: "cost/stationary/pc.php",
      method: "POST",
      data: {
        pc_mf: pc_mf,
        pc_model: pc_model,
        pc_cost: pc_cost,
        pannel_costing_id: pannel_costing_id,
      },
    });
  } else {
    Swal.fire("Please Enter All Values First");
  }
});
$(document).on("click", ".pc_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
    $.ajax({
      url: "cost/stationary/pc_del.php",
      method: "POST",
      data: {pannel_costing_id,
      },
      success: function (data) {
        $("#pc_mf").val("");
        $(".pc_model").val("");
        $(".pc_cost").val("");
      },
    });
    $("#twenty_nine").css("background-color", "rgb(187, 187, 187)");
});


$(document).on("click", ".polish_delete", function () {
  event.preventDefault();
     $(".polish_cost").val("");
});
$(document).on("click", ".rent_delete", function () {
  event.preventDefault();
     $(".rent_cost").val("");
});
$(document).on("click", ".wc_delete", function () {
  event.preventDefault();
     $(".wc_cost").val("");
});
$(document).on("click", ".lc_delete", function () {
  event.preventDefault();
     $(".lc_cost").val("");
});
$(document).on("click", ".me_delete", function () {
  event.preventDefault();
     $(".me_cost").val("");
});

$(document).on("click", "#t_smg_percent", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let t_smg_value= $(".t_smg_percent").val();
  let t_smg_percent = $(".t_smg_percent").val();
  t_smg_percent = 1 + t_smg_percent / 100;

  let polish = $(".polish_cost").val();
  if (polish != "") {
  } else {
    polish = 0;
  }
  let rent = $(".rent_cost").val();
  if (rent != "") {
  } else {
    rent = 0;
  }
  let wc_cost = $(".wc_cost").val();
  if (wc_cost != "") {
  } else {
    wc_cost = 0;
  }
  let lc_cost = $(".lc_cost").val();
  if (lc_cost != "") {
  } else {
    lc_cost = 0;
  }
  let me_cost = $(".me_cost").val();
  if (me_cost != "") {
  } else {
    me_cost = 0;
  }
  let pl_cost = $(".pl_cost").val();
  if (pl_cost != "") {
  } else {
    pl_cost = 0;
  }
  let h_cost = $(".h_cost").val();
  if (h_cost != "") {
  } else {
    h_cost = 0;
  }
  let as_cost = $(".as_cost").val();
  if (as_cost != "") {
  } else {
    as_cost = 0;
  }
  let gk_cost = $(".gk_cost").val();
  if (gk_cost != "") {
  } else {
    gk_cost = 0;
  }
  let i_cost = $(".i_cost").val();
  if (i_cost != "") {
  } else {
    i_cost = 0;
  }
  let cd_cost = $(".cd_cost").val();
  if (cd_cost != "") {
  } else {
    cd_cost = 0;
  }
  let pc_cost = $(".pc_cost").val();
  if (pc_cost != "") {
  } else {
    pc_cost = 0;
  }
  let sixtn_SWG = $(".twelve_SWG").val();
  if (sixtn_SWG != "") {
  } else {
    sixtn_SWG = 0;
  }
  let bbr_total_cost = $(".bbr_total_cost").val();
  if (bbr_total_cost != "") {
  } else {
    bbr_total_cost = 0;
  }
  let tin_cost = $(".tin_cost").val();
  if (tin_cost != "") {
  } else {
    tin_cost = 0;
  }
  let total_sleeve_cost = $(".total_sleeve_cost").val();
  if (total_sleeve_cost != "") {
  } else {
    total_sleeve_cost = 0;
  }

  let cp_tsmg =
    parseInt(polish) +
    parseInt(rent) +
    parseInt(pl_cost) +
    parseInt(h_cost) +
    parseInt(as_cost) +
    parseInt(gk_cost) +
    parseInt(i_cost) +
    parseInt(cd_cost) +
    parseInt(pc_cost) +
    parseInt(wc_cost) +
    parseInt(lc_cost) +
    parseInt(me_cost) +
    parseInt(sixtn_SWG) +
    parseInt(bbr_total_cost) +
    parseInt(tin_cost) +
    parseInt(total_sleeve_cost);
  let pp_tsmg = cp_tsmg * t_smg_percent;

  $(".cp_tsmg").val(cp_tsmg);
  $(".pp_tsmg").val(pp_tsmg);

  $.ajax({
    url: "cost/stationary/t_smg_percent.php",
    method: "POST",
    data: {
      pannel_costing_id: pannel_costing_id,
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      t_smg_percent: t_smg_value,
      cp_tsmg: cp_tsmg,
      pp_tsmg: pp_tsmg,
    },
  });
  $("#thirty_one").css("background-color", "rgb(13, 211, 102)");
});
$(document).on("click", ".t_smg_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let polish = $(".polish_cost").val();
  let rent = $(".rent_cost").val();
  let wc_cost = $(".wc_cost").val();
  let lc_cost = $(".lc_cost").val();
  let me_cost = $(".me_cost").val();
  $.ajax({
    url: "cost/stationary/t_smg_del.php",
    method: "POST",
    data: {
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      pannel_costing_id: pannel_costing_id,
    },
    success: function (data) {
      $(".t_smg_percent").val("");
      $(".cp_tsmg").val("");
      $(".pp_tsmg").val("");
    },
  });
});

// For calcuation of STATIONARAY COST pannel lock
$(document).on("click", "#f_smg_percent", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let t_smg_value= $(".f_smg_percent").val();
  let f_smg_percent = $(".f_smg_percent").val();
  f_smg_percent = 1 + f_smg_percent / 100;

  let polish = $(".polish_cost").val();
  if (polish != "") {
  } else {
    polish = 0;
  }
  let rent = $(".rent_cost").val();
  if (rent != "") {
  } else {
    rent = 0;
  }
  let wc_cost = $(".wc_cost").val();
  if (wc_cost != "") {
  } else {
    wc_cost = 0;
  }
  let lc_cost = $(".lc_cost").val();
  if (lc_cost != "") {
  } else {
    lc_cost = 0;
  }
  let me_cost = $(".me_cost").val();
  if (me_cost != "") {
  } else {
    me_cost = 0;
  }
  let pl_cost = $(".pl_cost").val();
  if (pl_cost != "") {
  } else {
    pl_cost = 0;
  }
  let h_cost = $(".h_cost").val();
  if (h_cost != "") {
  } else {
    h_cost = 0;
  }
  let as_cost = $(".as_cost").val();
  if (as_cost != "") {
  } else {
    as_cost = 0;
  }
  let gk_cost = $(".gk_cost").val();
  if (gk_cost != "") {
  } else {
    gk_cost = 0;
  }
  let i_cost = $(".i_cost").val();
  if (i_cost != "") {
  } else {
    i_cost = 0;
  }
  let cd_cost = $(".cd_cost").val();
  if (cd_cost != "") {
  } else {
    cd_cost = 0;
  }
  let pc_cost = $(".pc_cost").val();
  if (pc_cost != "") {
  } else {
    pc_cost = 0;
  }
  let sixtn_SWG = $(".f_SWG").val();
  if (sixtn_SWG != "") {
  } else {
    sixtn_SWG = 0;
  }
  let bbr_total_cost = $(".bbr_total_cost").val();
  if (bbr_total_cost != "") {
  } else {
    bbr_total_cost = 0;
  }
  let tin_cost = $(".tin_cost").val();
  if (tin_cost != "") {
  } else {
    tin_cost = 0;
  }
  let total_sleeve_cost = $(".total_sleeve_cost").val();
  if (total_sleeve_cost != "") {
  } else {
    total_sleeve_cost = 0;
  }

  let cp_fsmg =
    parseInt(polish) +
    parseInt(rent) +
    parseInt(pl_cost) +
    parseInt(h_cost) +
    parseInt(as_cost) +
    parseInt(gk_cost) +
    parseInt(i_cost) +
    parseInt(cd_cost) +
    parseInt(pc_cost) +
    parseInt(wc_cost) +
    parseInt(lc_cost) +
    parseInt(me_cost) +
    parseInt(sixtn_SWG) +
    parseInt(bbr_total_cost) +
    parseInt(tin_cost) +
    parseInt(total_sleeve_cost);
  let pp_fsmg = cp_fsmg * f_smg_percent;
  $(".cp_fsmg").val(cp_fsmg);
  $(".pp_fsmg").val(pp_fsmg);

  $.ajax({
    url: "cost/stationary/f_smg_percent.php",
    method: "POST",
    data: {
      pannel_costing_id: pannel_costing_id,
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      f_smg_percent: t_smg_value,
      cp_fsmg: cp_fsmg,
      pp_fsmg: pp_fsmg,
    },
  });
  $("#thirty_one").css("background-color", "rgb(13, 211, 102)");
});
$(document).on("click", ".f_smg_del", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let polish = $(".polish_cost").val();
  let rent = $(".rent_cost").val();
  let wc_cost = $(".wc_cost").val();
  let lc_cost = $(".lc_cost").val();
  let me_cost = $(".me_cost").val();
  
  $.ajax({
    url: "cost/stationary/f_smg_del.php",
    method: "POST",
    data: {
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      pannel_costing_id: pannel_costing_id,
    },
    success: function (data) {
      $(".f_smg_percent").val("");
      $(".cp_fsmg").val("");
      $(".pp_fsmg").val("");
    },
  });
});

$(document).on("click", "#s_smg_percent", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let t_smg_value= $(".s_smg_percent").val();
  let s_smg_percent = $(".s_smg_percent").val();
  s_smg_percent = 1 + s_smg_percent / 100;

  let polish = $(".polish_cost").val();
  if (polish != "") {
  } else {
    polish = 0;
  }
  let rent = $(".rent_cost").val();
  if (rent != "") {
  } else {
    rent = 0;
  }
  let wc_cost = $(".wc_cost").val();
  if (wc_cost != "") {
  } else {
    wc_cost = 0;
  }
  let lc_cost = $(".lc_cost").val();
  if (lc_cost != "") {
  } else {
    lc_cost = 0;
  }
  let pl_cost = $(".pl_cost").val();
  if (pl_cost != "") {
  } else {
    pl_cost = 0;
  }
  let h_cost = $(".h_cost").val();
  if (h_cost != "") {
  } else {
    h_cost = 0;
  }
  let as_cost = $(".as_cost").val();
  if (as_cost != "") {
  } else {
    as_cost = 0;
  }
  let gk_cost = $(".gk_cost").val();
  if (gk_cost != "") {
  } else {
    gk_cost = 0;
  }
  let i_cost = $(".i_cost").val();
  if (i_cost != "") {
  } else {
    i_cost = 0;
  }
  let cd_cost = $(".cd_cost").val();
  if (cd_cost != "") {
  } else {
    cd_cost = 0;
  }
  let pc_cost = $(".pc_cost").val();
  if (pc_cost != "") {
  } else {
    pc_cost = 0;
  }
  let me_cost = $(".me_cost").val();
  if (me_cost != "") {
  } else {
    me_cost = 0;
  }
  let sixtn_SWG = $(".sixtn_SWG").val();
  if (sixtn_SWG != "") {
  } else {
    sixtn_SWG = 0;
  }
  let bbr_total_cost = $(".bbr_total_cost").val();
  if (bbr_total_cost != "") {
  } else {
    bbr_total_cost = 0;
  }
  let tin_cost = $(".tin_cost").val();
  if (tin_cost != "") {
  } else {
    tin_cost = 0;
  }
  let total_sleeve_cost = $(".total_sleeve_cost").val();
  if (total_sleeve_cost != "") {
  } else {
    total_sleeve_cost = 0;
  }

  let cp_ssmg =
    parseInt(polish) +
    parseInt(rent) +
    parseInt(pl_cost) +
    parseInt(h_cost) +
    parseInt(as_cost) +
    parseInt(gk_cost) +
    parseInt(i_cost) +
    parseInt(cd_cost) +
    parseInt(pc_cost) +
    parseInt(wc_cost) +
    parseInt(lc_cost) +
    parseInt(me_cost) +
    parseInt(sixtn_SWG) +
    parseInt(bbr_total_cost) +
    parseInt(tin_cost) +
    parseInt(total_sleeve_cost);
  let pp_ssmg = cp_ssmg * s_smg_percent;

  $(".cp_ssmg").val(cp_ssmg);
  $(".pp_ssmg").val(pp_ssmg);
  $.ajax({
    url: "cost/stationary/s_smg_percent.php",
    method: "POST",
    data: {
      pannel_costing_id: pannel_costing_id,
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      s_smg_percent:t_smg_value,
      cp_ssmg: cp_ssmg,
      pp_ssmg: pp_ssmg,
    },
  });
  $("#thirty_one").css("background-color", "rgb(13, 211, 102)");
});
$(document).on("click", ".s_smg_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let polish = $(".polish_cost").val();
  let rent = $(".rent_cost").val();
  let wc_cost = $(".wc_cost").val();
  let lc_cost = $(".lc_cost").val();
  let me_cost = $(".me_cost").val();
  $.ajax({
    url: "cost/stationary/s_smg_del.php",
    method: "POST",
    data: {
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      pannel_costing_id: pannel_costing_id,
    },
    success: function (data) {
      $(".s_smg_percent").val("");
      $(".cp_ssmg").val("");
      $(".pp_ssmg").val("");
    },
  });
});

$(document).on("click", "#e_smg_percent", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let t_smg_value= $(".e_smg_percent").val();
  let e_smg_percent = $(".e_smg_percent").val();
  e_smg_percent = 1 + e_smg_percent / 100;

  let polish = $(".polish_cost").val();
  if (polish != "") {
  } else {
    polish = 0;
  }
  let rent = $(".rent_cost").val();
  if (rent != "") {
  } else {
    rent = 0;
  }
  let wc_cost = $(".wc_cost").val();
  if (wc_cost != "") {
  } else {
    wc_cost = 0;
  }
  let lc_cost = $(".lc_cost").val();
  if (lc_cost != "") {
  } else {
    lc_cost = 0;
  }
  let me_cost = $(".me_cost").val();
  if (me_cost != "") {
  } else {
    me_cost = 0;
  }
  let pl_cost = $(".pl_cost").val();
  if (pl_cost != "") {
  } else {
    pl_cost = 0;
  }
  let h_cost = $(".h_cost").val();
  if (h_cost != "") {
  } else {
    h_cost = 0;
  }
  let as_cost = $(".as_cost").val();
  if (as_cost != "") {
  } else {
    as_cost = 0;
  }
  let gk_cost = $(".gk_cost").val();
  if (gk_cost != "") {
  } else {
    gk_cost = 0;
  }
  let i_cost = $(".i_cost").val();
  if (i_cost != "") {
  } else {
    i_cost = 0;
  }
  let cd_cost = $(".cd_cost").val();
  if (cd_cost != "") {
  } else {
    cd_cost = 0;
  }
  let pc_cost = $(".pc_cost").val();
  if (pc_cost != "") {
  } else {
    pc_cost = 0;
  }
  let sixtn_SWG = $(".atn_SMG").val();
  if (sixtn_SWG != "") {
  } else {
    sixtn_SWG = 0;
  }
  let bbr_total_cost = $(".bbr_total_cost").val();
  if (bbr_total_cost != "") {
  } else {
    bbr_total_cost = 0;
  }
  let tin_cost = $(".tin_cost").val();
  if (tin_cost != "") {
  } else {
    tin_cost = 0;
  }
  let total_sleeve_cost = $(".total_sleeve_cost").val();
  if (total_sleeve_cost != "") {
  } else {
    total_sleeve_cost = 0;
  }
  let cp_esmg =
    parseInt(polish) +
    parseInt(rent) +
    parseInt(pl_cost) +
    parseInt(h_cost) +
    parseInt(as_cost) +
    parseInt(gk_cost) +
    parseInt(i_cost) +
    parseInt(cd_cost) +
    parseInt(pc_cost) +
    parseInt(wc_cost) +
    parseInt(lc_cost) +
    parseInt(me_cost) +
    parseInt(sixtn_SWG) +
    parseInt(bbr_total_cost) +
    parseInt(tin_cost) +
    parseInt(total_sleeve_cost);
  let pp_esmg = cp_esmg * e_smg_percent;

  $(".cp_esmg").val(cp_esmg);
  $(".pp_esmg").val(pp_esmg);

  $.ajax({
    url: "cost/stationary/e_smg_percent.php",
    method: "POST",
    data: {
      pannel_costing_id: pannel_costing_id,
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      e_smg_percent: t_smg_value,
      cp_esmg: cp_esmg,
      pp_esmg: pp_esmg,
    },
  });
  $("#thirty_one").css("background-color", "rgb(13, 211, 102)");
});
$(document).on("click", ".e_smg_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let polish = $(".polish_cost").val();
  let rent = $(".rent_cost").val();
  let wc_cost = $(".wc_cost").val();
  let lc_cost = $(".lc_cost").val();
  let me_cost = $(".me_cost").val();
  $.ajax({
    url: "cost/stationary/e_smg_del.php",
    method: "POST",
    data: {
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      pannel_costing_id: pannel_costing_id,
    },
    success: function (data) {
      $(".e_smg_percent").val("");
      $(".cp_esmg").val("");
      $(".pp_esmg").val("");
    },
  });
});

$(document).on("click", "#ty_smg_percent", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let t_smg_value= $(".ty_smg_percent").val();
  let ty_smg_percent = $(".ty_smg_percent").val();
  ty_smg_percent = 1 + ty_smg_percent / 100;

  let polish = $(".polish_cost").val();
  if (polish != "") {
  } else {
    polish = 0;
  }
  let rent = $(".rent_cost").val();
  if (rent != "") {
  } else {
    rent = 0;
  }
  let wc_cost = $(".wc_cost").val();
  if (wc_cost != "") {
  } else {
    wc_cost = 0;
  }
  let lc_cost = $(".lc_cost").val();
  if (lc_cost != "") {
  } else {
    lc_cost = 0;
  }
  let me_cost = $(".me_cost").val();
  if (me_cost != "") {
  } else {
    me_cost = 0;
  }
  let pl_cost = $(".pl_cost").val();
  if (pl_cost != "") {
  } else {
    pl_cost = 0;
  }
  let h_cost = $(".h_cost").val();
  if (h_cost != "") {
  } else {
    h_cost = 0;
  }
  let as_cost = $(".as_cost").val();
  if (as_cost != "") {
  } else {
    as_cost = 0;
  }
  let gk_cost = $(".gk_cost").val();
  if (gk_cost != "") {
  } else {
    gk_cost = 0;
  }
  let i_cost = $(".i_cost").val();
  if (i_cost != "") {
  } else {
    i_cost = 0;
  }
  let cd_cost = $(".cd_cost").val();
  if (cd_cost != "") {
  } else {
    cd_cost = 0;
  }
  let pc_cost = $(".pc_cost").val();
  if (pc_cost != "") {
  } else {
    pc_cost = 0;
  }
  let sixtn_SWG = $(".twenty_SMG").val();
  if (sixtn_SWG != "") {
  } else {
    sixtn_SWG = 0;
  }
  let bbr_total_cost = $(".bbr_total_cost").val();
  if (bbr_total_cost != "") {
  } else {
    bbr_total_cost = 0;
  }
  let tin_cost = $(".tin_cost").val();
  if (tin_cost != "") {
  } else {
    tin_cost = 0;
  }
  let total_sleeve_cost = $(".total_sleeve_cost").val();
  if (total_sleeve_cost != "") {
  } else {
    total_sleeve_cost = 0;
  }

  let cp_tysmg =
    parseInt(polish) +
    parseInt(rent) +
    parseInt(pl_cost) +
    parseInt(h_cost) +
    parseInt(as_cost) +
    parseInt(gk_cost) +
    parseInt(i_cost) +
    parseInt(cd_cost) +
    parseInt(pc_cost) +
    parseInt(wc_cost) +
    parseInt(lc_cost) +
    parseInt(me_cost) +
    parseInt(sixtn_SWG) +
    parseInt(bbr_total_cost) +
    parseInt(tin_cost) +
    parseInt(total_sleeve_cost);
  let pp_tysmg = cp_tysmg * ty_smg_percent;

  $(".cp_tysmg").val(cp_tysmg);
  $(".pp_tysmg").val(pp_tysmg);

  $.ajax({
    url: "cost/stationary/ty_smg_percent.php",
    method: "POST",
    data: {
      pannel_costing_id: pannel_costing_id,
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      ty_smg_percent: t_smg_value,
      cp_tysmg: cp_tysmg,
      pp_tysmg: pp_tysmg,
    },
  });
  $("#thirty_one").css("background-color", "rgb(13, 211, 102)");
});
$(document).on("click", ".ty_smg_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let polish = $(".polish_cost").val();
  let rent = $(".rent_cost").val();
  let wc_cost = $(".wc_cost").val();
  let lc_cost = $(".lc_cost").val();
  let me_cost = $(".me_cost").val();
  $.ajax({
    url: "cost/stationary/ty_smg_del.php",
    method: "POST",
    data: {
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      pannel_costing_id: pannel_costing_id,
    },
    success: function (data) {
      $(".ty_smg_percent").val("");
      $(".cp_tysmg").val("");
      $(".pp_tysmg").val("");
    },
  });
});

$(document).on("click", "#mult_gauge_percent", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let t_smg_value= $(".mult_gauge_percent").val();
  let ml_smg_percent = $(".mult_gauge_percent").val();
  ml_smg_percent = 1 + ml_smg_percent / 100;

  let polish = $(".polish_cost").val();
  if (polish != "") {
  } else {
    polish = 0;
  }
  let rent = $(".rent_cost").val();
  if (rent != "") {
  } else {
    rent = 0;
  }
  let wc_cost = $(".wc_cost").val();
  if (wc_cost != "") {
  } else {
    wc_cost = 0;
  }
  let lc_cost = $(".lc_cost").val();
  if (lc_cost != "") {
  } else {
    lc_cost = 0;
  }
  let me_cost = $(".me_cost").val();
  if (me_cost != "") {
  } else {
    me_cost = 0;
  }
  let pl_cost = $(".pl_cost").val();
  if (pl_cost != "") {
  } else {
    pl_cost = 0;
  }
  let h_cost = $(".h_cost").val();
  if (h_cost != "") {
  } else {
    h_cost = 0;
  }
  let as_cost = $(".as_cost").val();
  if (as_cost != "") {
  } else {
    as_cost = 0;
  }
  let gk_cost = $(".gk_cost").val();
  if (gk_cost != "") {
  } else {
    gk_cost = 0;
  }
  let i_cost = $(".i_cost").val();
  if (i_cost != "") {
  } else {
    i_cost = 0;
  }
  let cd_cost = $(".cd_cost").val();
  if (cd_cost != "") {
  } else {
    cd_cost = 0;
  }
  let pc_cost = $(".pc_cost").val();
  if (pc_cost != "") {
  } else {
    pc_cost = 0;
  }
  let sixtn_SWG = $(".mlt_gauge_pr").val();
  if (sixtn_SWG != "") {
  } else {
    sixtn_SWG = 0;
  }
  let bbr_total_cost = $(".bbr_total_cost").val();
  if (bbr_total_cost != "") {
  } else {
    bbr_total_cost = 0;
  }
  let tin_cost = $(".tin_cost").val();
  if (tin_cost != "") {
  } else {
    tin_cost = 0;
  }
  let total_sleeve_cost = $(".total_sleeve_cost").val();
  if (total_sleeve_cost != "") {
  } else {
    total_sleeve_cost = 0;
  }

  let cp_mult_gauge =
    parseInt(polish) +
    parseInt(rent) +
    parseInt(pl_cost) +
    parseInt(h_cost) +
    parseInt(as_cost) +
    parseInt(gk_cost) +
    parseInt(i_cost) +
    parseInt(cd_cost) +
    parseInt(pc_cost) +
    parseInt(wc_cost) +
    parseInt(lc_cost) +
    parseInt(me_cost) +
    parseInt(sixtn_SWG) +
    parseInt(bbr_total_cost) +
    parseInt(tin_cost) +
    parseInt(total_sleeve_cost);
  let pp_mult_gauge = cp_mult_gauge * ml_smg_percent;

  $(".cp_mult_gauge").val(cp_mult_gauge);
  $(".pp_mult_gauge").val(pp_mult_gauge);

  $.ajax({
    url: "cost/stationary/mult_smg_percent.php",
    method: "POST",
    data: {
      pannel_costing_id: pannel_costing_id,
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      ml_smg_percent: t_smg_value,
      cp_mult_gauge: cp_mult_gauge,
      pp_mult_gauge: pp_mult_gauge,
    },
  });
  $("#thirty_one").css("background-color", "rgb(13, 211, 102)");
});
$(document).on("click", ".mult_gauge_delete", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let polish = $(".polish_cost").val();
  let rent = $(".rent_cost").val();
  let wc_cost = $(".wc_cost").val();
  let lc_cost = $(".lc_cost").val();
  let me_cost = $(".me_cost").val();
  $.ajax({
    url: "cost/stationary/mult_gauge_del.php",
    method: "POST",
    data: {
      polish: polish,
      rent: rent,
      wc_cost: wc_cost,
      lc_cost: lc_cost,
      me_cost: me_cost,
      pannel_costing_id: pannel_costing_id,
    },
    success: function (data) {
      $(".mult_gauge_percent").val("");
      $(".cp_mult_gauge").val("");
      $(".pp_mult_gauge").val("");
    },
  });
});

// For calcuation of STATIONARAY COST pannel lock
$(document).on("click", "#first_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let dimesnsion = document.getElementById("first_dms").value;
  let first_foot_size = document.getElementById("first_foot_size").value;
  let first_sleeve_cost = document.getElementById("first_sleeve_cost").value;
  let first_factor = document.getElementById("first_factor").value;
  if (first_foot_size != "" && first_sleeve_cost != "" && first_factor != "") {
    let first_cost = first_foot_size * first_sleeve_cost * first_factor;
    $(".first_cost").val(first_cost);

    if (dimesnsion == "20*5") {
      let first_bbr_weight = first_foot_size * 0.3;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".20_5_foot_size").val(first_foot_size);
      $(".20_5_sleeve_cost").val(first_sleeve_cost);
      $(".20_5_factor").val(first_factor);
      $(".20_5_sleeve_total_cost").val(first_cost);
      $(".20_5_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/t_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });

    } else if (dimesnsion == "25*5") {
      let first_bbr_weight = first_foot_size * 0.4;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".25_5_foot_size").val(first_foot_size);
      $(".25_5_sleeve_cost").val(first_sleeve_cost);
      $(".25_5_factor").val(first_factor);
      $(".25_5_sleeve_total_cost").val(first_cost);
      $(".25_5_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "25*10") {
      let first_bbr_weight = first_foot_size * 0.7;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".25_10_foot_size").val(first_foot_size);
      $(".25_10_sleeve_cost").val(first_sleeve_cost);
      $(".25_10_factor").val(first_factor);
      $(".25_10_sleeve_total_cost").val(first_cost);
      $(".25_10_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "30*5") {
      let first_bbr_weight = first_foot_size * 0.45;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".30_5_foot_size").val(first_foot_size);
      $(".30_5_sleeve_cost").val(first_sleeve_cost);
      $(".30_5_factor").val(first_factor);
      $(".30_5_sleeve_total_cost").val(first_cost);
      $(".30_5_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "30*10") {
      let first_bbr_weight = first_foot_size * 0.85;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".30_10_foot_size").val(first_foot_size);
      $(".30_10_sleeve_cost").val(first_sleeve_cost);
      $(".30_10_factor").val(first_factor);
      $(".30_10_sleeve_total_cost").val(first_cost);
      $(".30_10_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "40*5") {
      let first_bbr_weight = first_foot_size * 0.6;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".40_5_foot_size").val(first_foot_size);
      $(".40_5_sleeve_cost").val(first_sleeve_cost);
      $(".40_5_factor").val(first_factor);
      $(".40_5_sleeve_total_cost").val(first_cost);
      $(".40_5_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "40*10") {
      let first_bbr_weight = first_foot_size * 1.1;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".40_10_foot_size").val(first_foot_size);
      $(".40_10_sleeve_cost").val(first_sleeve_cost);
      $(".40_10_factor").val(first_factor);
      $(".40_10_sleeve_total_cost").val(first_cost);
      $(".40_10_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "50*5") {
      let first_bbr_weight = first_foot_size * 0.7;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".50_5_foot_size").val(first_foot_size);
      $(".50_5_sleeve_cost").val(first_sleeve_cost);
      $(".50_5_factor").val(first_factor);
      $(".50_5_sleeve_total_cost").val(first_cost);
      $(".50_5_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "50*10") {
      let first_bbr_weight = first_foot_size * 1.4;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".50_10_foot_size").val(first_foot_size);
      $(".50_10_sleeve_cost").val(first_sleeve_cost);
      $(".50_10_factor").val(first_factor);
      $(".50_10_sleeve_total_cost").val(first_cost);
      $(".50_10_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "60*5") {
      let first_bbr_weight = first_foot_size * 0.85;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".60_5_foot_size").val(first_foot_size);
      $(".60_5_sleeve_cost").val(first_sleeve_cost);
      $(".60_5_factor").val(first_factor);
      $(".60_5_sleeve_total_cost").val(first_cost);
      $(".60_5_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "60*10") {
      let first_bbr_weight = first_foot_size * 1.7;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".60_10_foot_size").val(first_foot_size);
      $(".60_10_sleeve_cost").val(first_sleeve_cost);
      $(".60_10_factor").val(first_factor);
      $(".60_10_sleeve_total_cost").val(first_cost);
      $(".60_10_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "80*5") {
      let first_bbr_weight = first_foot_size * 1.1;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".80_5_foot_size").val(first_foot_size);
      $(".80_5_sleeve_cost").val(first_sleeve_cost);
      $(".80_5_factor").val(first_factor);
      $(".80_5_sleeve_total_cost").val(first_cost);
      $(".80_5_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "80*10") {
      let first_bbr_weight = first_foot_size * 2.2;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".80_10_foot_size").val(first_foot_size);
      $(".80_10_sleeve_cost").val(first_sleeve_cost);
      $(".80_10_factor").val(first_factor);
      $(".80_10_sleeve_total_cost").val(first_cost);
      $(".80_10_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "100*5") {
      let first_bbr_weight = first_foot_size * 1.4;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".100_5_foot_size").val(first_foot_size);
      $(".100_5_sleeve_cost").val(first_sleeve_cost);
      $(".100_5_factor").val(first_factor);
      $(".100_5_sleeve_total_cost").val(first_cost);
      $(".100_5_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "100*10") {
      let first_bbr_weight = first_foot_size * 2.8;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".100_10_foot_size").val(first_foot_size);
      $(".100_10_sleeve_cost").val(first_sleeve_cost);
      $(".100_10_factor").val(first_factor);
      $(".100_10_sleeve_total_cost").val(first_cost);
      $(".100_10_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "120*5") {
      let first_bbr_weight = first_foot_size * 1.67;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".120_5_foot_size").val(first_foot_size);
      $(".120_5_sleeve_cost").val(first_sleeve_cost);
      $(".120_5_factor").val(first_factor);
      $(".120_5_sleeve_total_cost").val(first_cost);
      $(".120_5_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "120*10") {
      let first_bbr_weight = first_foot_size * 3.3;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".120_10_foot_size").val(first_foot_size);
      $(".120_10_sleeve_cost").val(first_sleeve_cost);
      $(".120_10_factor").val(first_factor);
      $(".120_10_sleeve_total_cost").val(first_cost);
      $(".120_10_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });
    } else if (dimesnsion == "150*10") {
      let first_bbr_weight = first_foot_size * 4.2;
      $(".first_bbr_weight").val(first_bbr_weight);
      $(".150_10_foot_size").val(first_foot_size);
      $(".150_10_sleeve_cost").val(first_sleeve_cost);
      $(".150_10_factor").val(first_factor);
      $(".150_10_sleeve_total_cost").val(first_cost);
      $(".150_10_bbr_weight").val(first_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/of_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:first_foot_size,
          first_sleeve_cost:first_sleeve_cost,
          first_factor:first_factor,
          first_cost:first_cost,
          first_bbr_weight:first_bbr_weight
        }
      });

      $("#twenty_one").css("background-color", "rgb(13, 211, 102)");
    }
  } else {
    Swal.fire("Please Enter All Values First");
  }
});

$(document).on("click", "#second_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let dimesnsion = document.getElementById("second_dms").value;
  let second_foot_size = document.getElementById("second_foot_size").value;
  let second_sleeve_cost = document.getElementById("second_sleeve_cost").value;
  let second_factor = document.getElementById("second_factor").value;
  if (
    second_foot_size != "" &&
    second_sleeve_cost != "" &&
    second_factor != ""
  ) {
    let second_cost = second_foot_size * second_sleeve_cost * second_factor;
    $(".second_cost").val(second_cost);

    if (dimesnsion == "20*5") {
      let second_bbr_weight = second_foot_size * 0.3;
      $(".second_bbr_weight").val(second_bbr_weight);

      $(".20_5_foot_size").val(second_foot_size);
      $(".20_5_sleeve_cost").val(second_sleeve_cost);
      $(".20_5_factor").val(second_factor);
      $(".20_5_sleeve_total_cost").val(second_cost);
      $(".20_5_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/t_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
      
    } else if (dimesnsion == "25*5") {
      let second_bbr_weight = second_foot_size * 0.4;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".25_5_foot_size").val(second_foot_size);
      $(".25_5_sleeve_cost").val(second_sleeve_cost);
      $(".25_5_factor").val(second_factor);
      $(".25_5_sleeve_total_cost").val(second_cost);
      $(".25_5_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "25*10") {
      let second_bbr_weight = second_foot_size * 0.7;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".25_10_foot_size").val(second_foot_size);
      $(".25_10_sleeve_cost").val(second_sleeve_cost);
      $(".25_10_factor").val(second_factor);
      $(".25_10_sleeve_total_cost").val(second_cost);
      $(".25_10_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "30*5") {
      let second_bbr_weight = second_foot_size * 0.45;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".30_5_foot_size").val(second_foot_size);
      $(".30_5_sleeve_cost").val(second_sleeve_cost);
      $(".30_5_factor").val(second_factor);
      $(".30_5_sleeve_total_cost").val(second_cost);
      $(".30_5_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "30*10") {
      let second_bbr_weight = second_foot_size * 0.85;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".30_10_foot_size").val(second_foot_size);
      $(".30_10_sleeve_cost").val(second_sleeve_cost);
      $(".30_10_factor").val(second_factor);
      $(".30_10_sleeve_total_cost").val(second_cost);
      $(".30_10_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "40*5") {
      let second_bbr_weight = second_foot_size * 0.6;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".40_5_foot_size").val(second_foot_size);
      $(".40_5_sleeve_cost").val(second_sleeve_cost);
      $(".40_5_factor").val(second_factor);
      $(".40_5_sleeve_total_cost").val(second_cost);
      $(".40_5_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "40*10") {
      let second_bbr_weight = second_foot_size * 1.1;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".40_10_foot_size").val(second_foot_size);
      $(".40_10_sleeve_cost").val(second_sleeve_cost);
      $(".40_10_factor").val(second_factor);
      $(".40_10_sleeve_total_cost").val(second_cost);
      $(".40_10_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "50*5") {
      let second_bbr_weight = second_foot_size * 0.7;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".50_5_foot_size").val(second_foot_size);
      $(".50_5_sleeve_cost").val(second_sleeve_cost);
      $(".50_5_factor").val(second_factor);
      $(".50_5_sleeve_total_cost").val(second_cost);
      $(".50_5_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "50*10") {
      let second_bbr_weight = second_foot_size * 1.4;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".50_10_foot_size").val(second_foot_size);
      $(".50_10_sleeve_cost").val(second_sleeve_cost);
      $(".50_10_factor").val(second_factor);
      $(".50_10_sleeve_total_cost").val(second_cost);
      $(".50_10_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "60*5") {
      let second_bbr_weight = second_foot_size * 0.85;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".60_5_foot_size").val(second_foot_size);
      $(".60_5_sleeve_cost").val(second_sleeve_cost);
      $(".60_5_factor").val(second_factor);
      $(".60_5_sleeve_total_cost").val(second_cost);
      $(".60_5_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "60*10") {
      let second_bbr_weight = second_foot_size * 1.7;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".60_10_foot_size").val(second_foot_size);
      $(".60_10_sleeve_cost").val(second_sleeve_cost);
      $(".60_10_factor").val(second_factor);
      $(".60_10_sleeve_total_cost").val(second_cost);
      $(".60_10_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "80*5") {
      let second_bbr_weight = second_foot_size * 1.1;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".80_5_foot_size").val(second_foot_size);
      $(".80_5_sleeve_cost").val(second_sleeve_cost);
      $(".80_5_factor").val(second_factor);
      $(".80_5_sleeve_total_cost").val(second_cost);
      $(".80_5_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "80*10") {
      let second_bbr_weight = second_foot_size * 2.2;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".80_10_foot_size").val(second_foot_size);
      $(".80_10_sleeve_cost").val(second_sleeve_cost);
      $(".80_10_factor").val(second_factor);
      $(".80_10_sleeve_total_cost").val(second_cost);
      $(".80_10_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "100*5") {
      let second_bbr_weight = second_foot_size * 1.4;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".100_5_foot_size").val(second_foot_size);
      $(".100_5_sleeve_cost").val(second_sleeve_cost);
      $(".100_5_factor").val(second_factor);
      $(".100_5_sleeve_total_cost").val(second_cost);
      $(".100_5_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "100*10") {
      let second_bbr_weight = second_foot_size * 2.8;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".100_10_foot_size").val(second_foot_size);
      $(".100_10_sleeve_cost").val(second_sleeve_cost);
      $(".100_10_factor").val(second_factor);
      $(".100_10_sleeve_total_cost").val(second_cost);
      $(".100_10_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "120*5") {
      let second_bbr_weight = second_foot_size * 1.67;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".120_5_foot_size").val(second_foot_size);
      $(".120_5_sleeve_cost").val(second_sleeve_cost);
      $(".120_5_factor").val(second_factor);
      $(".120_5_sleeve_total_cost").val(second_cost);
      $(".120_5_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "120*10") {
      let second_bbr_weight = second_foot_size * 3.3;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".120_10_foot_size").val(second_foot_size);
      $(".120_10_sleeve_cost").val(second_sleeve_cost);
      $(".120_10_factor").val(second_factor);
      $(".120_10_sleeve_total_cost").val(second_cost);
      $(".120_10_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    } else if (dimesnsion == "150*10") {
      let second_bbr_weight = second_foot_size * 4.2;
      $(".second_bbr_weight").val(second_bbr_weight);
      $(".150_10_foot_size").val(second_foot_size);
      $(".150_10_sleeve_cost").val(second_sleeve_cost);
      $(".150_10_factor").val(second_factor);
      $(".150_10_sleeve_total_cost").val(second_cost);
      $(".150_10_bbr_weight").val(second_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/of_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:second_foot_size,
          first_sleeve_cost:second_sleeve_cost,
          first_factor:second_factor,
          first_cost:second_cost,
          first_bbr_weight:second_bbr_weight
        }
      });
    }
    $("#twenty_one").css("background-color", "rgb(13, 211, 102)");
  } else {
    Swal.fire("Please Enter All Values First");
  }
});

$(document).on("click", "#third_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let dimesnsion = document.getElementById("third_dms").value;
  let third_foot_size = document.getElementById("third_foot_size").value;
  let third_sleeve_cost = document.getElementById("third_sleeve_cost").value;
  let third_factor = document.getElementById("third_factor").value;
  if (third_foot_size != "" && third_sleeve_cost != "" && third_factor != "") {
    let third_cost = third_foot_size * third_sleeve_cost * third_factor;
    $(".third_cost").val(third_cost);

    if (dimesnsion == "20*5") {
      let third_bbr_weight = third_foot_size * 0.3;
      $(".third_bbr_weight").val(third_bbr_weight);

      $(".20_5_foot_size").val(third_foot_size);
      $(".20_5_sleeve_cost").val(third_sleeve_cost);
      $(".20_5_factor").val(third_factor);
      $(".20_5_sleeve_total_cost").val(third_cost);
      $(".20_5_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/t_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "25*5") {
      let third_bbr_weight = third_foot_size * 0.4;
      $(".third_bbr_weight").val(third_bbr_weight);

      $(".25_5_foot_size").val(third_foot_size);
      $(".25_5_sleeve_cost").val(third_sleeve_cost);
      $(".25_5_factor").val(third_factor);
      $(".25_5_sleeve_total_cost").val(third_cost);
      $(".25_5_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "25*10") {
      let third_bbr_weight = third_foot_size * 0.7;
      $(".third_bbr_weight").val(third_bbr_weight);

      $(".25_10_foot_size").val(third_foot_size);
      $(".25_10_sleeve_cost").val(third_sleeve_cost);
      $(".25_10_factor").val(third_factor);
      $(".25_10_sleeve_total_cost").val(third_cost);
      $(".25_10_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "30*5") {
      let third_bbr_weight = third_foot_size * 0.45;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".30_5_foot_size").val(third_foot_size);
      $(".30_5_sleeve_cost").val(third_sleeve_cost);
      $(".30_5_factor").val(third_factor);
      $(".30_5_sleeve_total_cost").val(third_cost);
      $(".30_5_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "30*10") {
      let third_bbr_weight = third_foot_size * 0.85;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".30_10_foot_size").val(third_foot_size);
      $(".30_10_sleeve_cost").val(third_sleeve_cost);
      $(".30_10_factor").val(third_factor);
      $(".30_10_sleeve_total_cost").val(third_cost);
      $(".30_10_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "40*5") {
      let third_bbr_weight = third_foot_size * 0.6;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".40_5_foot_size").val(third_foot_size);
      $(".40_5_sleeve_cost").val(third_sleeve_cost);
      $(".40_5_factor").val(third_factor);
      $(".40_5_sleeve_total_cost").val(third_cost);
      $(".40_5_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "40*10") {
      let third_bbr_weight = third_foot_size * 1.1;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".40_10_foot_size").val(third_foot_size);
      $(".40_10_sleeve_cost").val(third_sleeve_cost);
      $(".40_10_factor").val(third_factor);
      $(".40_10_sleeve_total_cost").val(third_cost);
      $(".40_10_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "50*5") {
      let third_bbr_weight = third_foot_size * 0.7;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".50_5_foot_size").val(third_foot_size);
      $(".50_5_sleeve_cost").val(third_sleeve_cost);
      $(".50_5_factor").val(third_factor);
      $(".50_5_sleeve_total_cost").val(third_cost);
      $(".50_5_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "50*10") {
      let third_bbr_weight = third_foot_size * 1.4;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".50_10_foot_size").val(third_foot_size);
      $(".50_10_sleeve_cost").val(third_sleeve_cost);
      $(".50_10_factor").val(third_factor);
      $(".50_10_sleeve_total_cost").val(third_cost);
      $(".50_10_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "60*5") {
      let third_bbr_weight = third_foot_size * 0.85;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".60_5_foot_size").val(third_foot_size);
      $(".60_5_sleeve_cost").val(third_sleeve_cost);
      $(".60_5_factor").val(third_factor);
      $(".60_5_sleeve_total_cost").val(third_cost);
      $(".60_5_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "60*10") {
      let third_bbr_weight = third_foot_size * 1.7;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".60_10_foot_size").val(third_foot_size);
      $(".60_10_sleeve_cost").val(third_sleeve_cost);
      $(".60_10_factor").val(third_factor);
      $(".60_10_sleeve_total_cost").val(third_cost);
      $(".60_10_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "80*5") {
      let third_bbr_weight = third_foot_size * 1.1;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".80_5_foot_size").val(third_foot_size);
      $(".80_5_sleeve_cost").val(third_sleeve_cost);
      $(".80_5_factor").val(third_factor);
      $(".80_5_sleeve_total_cost").val(third_cost);
      $(".80_5_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "80*10") {
      let third_bbr_weight = third_foot_size * 2.2;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".80_10_foot_size").val(third_foot_size);
      $(".80_10_sleeve_cost").val(third_sleeve_cost);
      $(".80_10_factor").val(third_factor);
      $(".80_10_sleeve_total_cost").val(third_cost);
      $(".80_10_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "100*5") {
      let third_bbr_weight = third_foot_size * 1.4;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".100_5_foot_size").val(third_foot_size);
      $(".100_5_sleeve_cost").val(third_sleeve_cost);
      $(".100_5_factor").val(third_factor);
      $(".100_5_sleeve_total_cost").val(third_cost);
      $(".100_5_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "100*10") {
      let third_bbr_weight = third_foot_size * 2.8;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".100_10_foot_size").val(third_foot_size);
      $(".100_10_sleeve_cost").val(third_sleeve_cost);
      $(".100_10_factor").val(third_factor);
      $(".100_10_sleeve_total_cost").val(third_cost);
      $(".100_10_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "120*5") {
      let third_bbr_weight = third_foot_size * 1.67;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".120_5_foot_size").val(third_foot_size);
      $(".120_5_sleeve_cost").val(third_sleeve_cost);
      $(".120_5_factor").val(third_factor);
      $(".120_5_sleeve_total_cost").val(third_cost);
      $(".120_5_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "120*10") {
      let third_bbr_weight = third_foot_size * 3.3;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".120_10_foot_size").val(third_foot_size);
      $(".120_10_sleeve_cost").val(third_sleeve_cost);
      $(".120_10_factor").val(third_factor);
      $(".120_10_sleeve_total_cost").val(third_cost);
      $(".120_10_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    } else if (dimesnsion == "150*10") {
      let third_bbr_weight = third_foot_size * 4.2;
      $(".third_bbr_weight").val(third_bbr_weight);
      $(".150_10_foot_size").val(third_foot_size);
      $(".150_10_sleeve_cost").val(third_sleeve_cost);
      $(".150_10_factor").val(third_factor);
      $(".150_10_sleeve_total_cost").val(third_cost);
      $(".150_10_bbr_weight").val(third_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/of_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:third_foot_size,
          first_sleeve_cost:third_sleeve_cost,
          first_factor:third_factor,
          first_cost:third_cost,
          first_bbr_weight:third_bbr_weight
        }
      });
    }
    $("#twenty_one").css("background-color", "rgb(13, 211, 102)");
  } else {
    Swal.fire("Please Enter All Values First");
  }
});

$(document).on("click", "#fourth_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let dimesnsion = document.getElementById("fourth_dms").value;
  let fourth_foot_size = document.getElementById("fourth_foot_size").value;
  let fourth_sleeve_cost = document.getElementById("fourth_sleeve_cost").value;
  let fourth_factor = document.getElementById("fourth_factor").value;
  if (
    fourth_foot_size != "" &&
    fourth_sleeve_cost != "" &&
    fourth_factor != ""
  ) {
    let fourth_cost = fourth_foot_size * fourth_sleeve_cost * fourth_factor;
    $(".fourth_cost").val(fourth_cost);

    if (dimesnsion == "20*5") {
      let fourth_bbr_weight = fourth_foot_size * 0.3;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);

      $(".20_5_foot_size").val(fourth_foot_size);
      $(".20_5_sleeve_cost").val(fourth_sleeve_cost);
      $(".20_5_factor").val(fourth_factor);
      $(".20_5_sleeve_total_cost").val(fourth_cost);
      $(".20_5_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/t_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "25*5") {
      let fourth_bbr_weight = fourth_foot_size * 0.4;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);

      $(".25_5_foot_size").val(fourth_foot_size);
      $(".25_5_sleeve_cost").val(fourth_sleeve_cost);
      $(".25_5_factor").val(fourth_factor);
      $(".25_5_sleeve_total_cost").val(fourth_cost);
      $(".25_5_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "25*10") {
      let fourth_bbr_weight = fourth_foot_size * 0.7;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);

      $(".25_10_foot_size").val(fourth_foot_size);
      $(".25_10_sleeve_cost").val(fourth_sleeve_cost);
      $(".25_10_factor").val(fourth_factor);
      $(".25_10_sleeve_total_cost").val(fourth_cost);
      $(".25_10_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "30*5") {
      let fourth_bbr_weight = fourth_foot_size * 0.45;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".30_5_foot_size").val(fourth_foot_size);
      $(".30_5_sleeve_cost").val(fourth_sleeve_cost);
      $(".30_5_factor").val(fourth_factor);
      $(".30_5_sleeve_total_cost").val(fourth_cost);
      $(".30_5_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "30*10") {
      let fourth_bbr_weight = fourth_foot_size * 0.85;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".30_10_foot_size").val(fourth_foot_size);
      $(".30_10_sleeve_cost").val(fourth_sleeve_cost);
      $(".30_10_factor").val(fourth_factor);
      $(".30_10_sleeve_total_cost").val(fourth_cost);
      $(".30_10_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "40*5") {
      let fourth_bbr_weight = fourth_foot_size * 0.6;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".40_5_foot_size").val(fourth_foot_size);
      $(".40_5_sleeve_cost").val(fourth_sleeve_cost);
      $(".40_5_factor").val(fourth_factor);
      $(".40_5_sleeve_total_cost").val(fourth_cost);
      $(".40_5_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "40*10") {
      let fourth_bbr_weight = fourth_foot_size * 1.1;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".40_10_foot_size").val(fourth_foot_size);
      $(".40_10_sleeve_cost").val(fourth_sleeve_cost);
      $(".40_10_factor").val(fourth_factor);
      $(".40_10_sleeve_total_cost").val(fourth_cost);
      $(".40_10_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "50*5") {
      let fourth_bbr_weight = fourth_foot_size * 0.7;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".50_5_foot_size").val(fourth_foot_size);
      $(".50_5_sleeve_cost").val(fourth_sleeve_cost);
      $(".50_5_factor").val(fourth_factor);
      $(".50_5_sleeve_total_cost").val(fourth_cost);
      $(".50_5_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "50*10") {
      let fourth_bbr_weight = fourth_foot_size * 1.4;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".50_10_foot_size").val(fourth_foot_size);
      $(".50_10_sleeve_cost").val(fourth_sleeve_cost);
      $(".50_10_factor").val(fourth_factor);
      $(".50_10_sleeve_total_cost").val(fourth_cost);
      $(".50_10_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "60*5") {
      let fourth_bbr_weight = fourth_foot_size * 0.85;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".60_5_foot_size").val(fourth_foot_size);
      $(".60_5_sleeve_cost").val(fourth_sleeve_cost);
      $(".60_5_factor").val(fourth_factor);
      $(".60_5_sleeve_total_cost").val(fourth_cost);
      $(".60_5_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "60*10") {
      let fourth_bbr_weight = fourth_foot_size * 1.7;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".60_10_foot_size").val(fourth_foot_size);
      $(".60_10_sleeve_cost").val(fourth_sleeve_cost);
      $(".60_10_factor").val(fourth_factor);
      $(".60_10_sleeve_total_cost").val(fourth_cost);
      $(".60_10_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "80*5") {
      let fourth_bbr_weight = fourth_foot_size * 1.1;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".80_5_foot_size").val(fourth_foot_size);
      $(".80_5_sleeve_cost").val(fourth_sleeve_cost);
      $(".80_5_factor").val(fourth_factor);
      $(".80_5_sleeve_total_cost").val(fourth_cost);
      $(".80_5_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "80*10") {
      let fourth_bbr_weight = fourth_foot_size * 2.2;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".80_10_foot_size").val(fourth_foot_size);
      $(".80_10_sleeve_cost").val(fourth_sleeve_cost);
      $(".80_10_factor").val(fourth_factor);
      $(".80_10_sleeve_total_cost").val(fourth_cost);
      $(".80_10_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "100*5") {
      let fourth_bbr_weight = fourth_foot_size * 1.4;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".100_5_foot_size").val(fourth_foot_size);
      $(".100_5_sleeve_cost").val(fourth_sleeve_cost);
      $(".100_5_factor").val(fourth_factor);
      $(".100_5_sleeve_total_cost").val(fourth_cost);
      $(".100_5_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "100*10") {
      let fourth_bbr_weight = fourth_foot_size * 2.8;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".100_10_foot_size").val(fourth_foot_size);
      $(".100_10_sleeve_cost").val(fourth_sleeve_cost);
      $(".100_10_factor").val(fourth_factor);
      $(".100_10_sleeve_total_cost").val(fourth_cost);
      $(".100_10_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "120*5") {
      let fourth_bbr_weight = fourth_foot_size * 1.67;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".120_5_foot_size").val(fourth_foot_size);
      $(".120_5_sleeve_cost").val(fourth_sleeve_cost);
      $(".120_5_factor").val(fourth_factor);
      $(".120_5_sleeve_total_cost").val(fourth_cost);
      $(".120_5_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "120*10") {
      let fourth_bbr_weight = fourth_foot_size * 3.3;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".120_10_foot_size").val(fourth_foot_size);
      $(".120_10_sleeve_cost").val(fourth_sleeve_cost);
      $(".120_10_factor").val(fourth_factor);
      $(".120_10_sleeve_total_cost").val(fourth_cost);
      $(".120_10_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    } else if (dimesnsion == "150*10") {
      let fourth_bbr_weight = fourth_foot_size * 4.2;
      $(".fourth_bbr_weight").val(fourth_bbr_weight);
      $(".150_10_foot_size").val(fourth_foot_size);
      $(".150_10_sleeve_cost").val(fourth_sleeve_cost);
      $(".150_10_factor").val(fourth_factor);
      $(".150_10_sleeve_total_cost").val(fourth_cost);
      $(".150_10_bbr_weight").val(fourth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/of_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fourth_foot_size,
          first_sleeve_cost:fourth_sleeve_cost,
          first_factor:fourth_factor,
          first_cost:fourth_cost,
          first_bbr_weight:fourth_bbr_weight
        }
      });
    }
    $("#twenty_one").css("background-color", "rgb(13, 211, 102)");
  } else {
    Swal.fire("Please Enter All Values First");
  }
});

$(document).on("click", "#fifth_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let dimesnsion = document.getElementById("fifth_dms").value;
  let fifth_foot_size = document.getElementById("fifth_foot_size").value;
  let fifth_sleeve_cost = document.getElementById("fifth_sleeve_cost").value;
  let fifth_factor = document.getElementById("fifth_factor").value;
  if (fifth_foot_size != "" && fifth_sleeve_cost != "" && fifth_factor != "") {
    let fifth_cost = fifth_foot_size * fifth_sleeve_cost * fifth_factor;
    $(".fifth_cost").val(fifth_cost);

    if (dimesnsion == "20*5") {
      let fifth_bbr_weight = fifth_foot_size * 0.3;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);

      $(".20_5_foot_size").val(fifth_foot_size);
      $(".20_5_sleeve_cost").val(fifth_sleeve_cost);
      $(".20_5_factor").val(fifth_factor);
      $(".20_5_sleeve_total_cost").val(fifth_cost);
      $(".20_5_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/t_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "25*5") {
      let fifth_bbr_weight = fifth_foot_size * 0.4;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);

      $(".25_5_foot_size").val(fifth_foot_size);
      $(".25_5_sleeve_cost").val(fifth_sleeve_cost);
      $(".25_5_factor").val(fifth_factor);
      $(".25_5_sleeve_total_cost").val(fifth_cost);
      $(".25_5_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "25*10") {
      let fifth_bbr_weight = fifth_foot_size * 0.7;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);

      $(".25_10_foot_size").val(fifth_foot_size);
      $(".25_10_sleeve_cost").val(fifth_sleeve_cost);
      $(".25_10_factor").val(fifth_factor);
      $(".25_10_sleeve_total_cost").val(fifth_cost);
      $(".25_10_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "30*5") {
      let fifth_bbr_weight = fifth_foot_size * 0.45;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".30_5_foot_size").val(fifth_foot_size);
      $(".30_5_sleeve_cost").val(fifth_sleeve_cost);
      $(".30_5_factor").val(fifth_factor);
      $(".30_5_sleeve_total_cost").val(fifth_cost);
      $(".30_5_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "30*10") {
      let fifth_bbr_weight = fifth_foot_size * 0.85;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".30_10_foot_size").val(fifth_foot_size);
      $(".30_10_sleeve_cost").val(fifth_sleeve_cost);
      $(".30_10_factor").val(fifth_factor);
      $(".30_10_sleeve_total_cost").val(fifth_cost);
      $(".30_10_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "40*5") {
      let fifth_bbr_weight = fifth_foot_size * 0.6;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".40_5_foot_size").val(fifth_foot_size);
      $(".40_5_sleeve_cost").val(fifth_sleeve_cost);
      $(".40_5_factor").val(fifth_factor);
      $(".40_5_sleeve_total_cost").val(fifth_cost);
      $(".40_5_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "40*10") {
      let fifth_bbr_weight = fifth_foot_size * 1.1;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".40_10_foot_size").val(fifth_foot_size);
      $(".40_10_sleeve_cost").val(fifth_sleeve_cost);
      $(".40_10_factor").val(fifth_factor);
      $(".40_10_sleeve_total_cost").val(fifth_cost);
      $(".40_10_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "50*5") {
      let fifth_bbr_weight = fifth_foot_size * 0.7;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".50_5_foot_size").val(fifth_foot_size);
      $(".50_5_sleeve_cost").val(fifth_sleeve_cost);
      $(".50_5_factor").val(fifth_factor);
      $(".50_5_sleeve_total_cost").val(fifth_cost);
      $(".50_5_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "50*10") {
      let fifth_bbr_weight = fifth_foot_size * 1.4;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".50_10_foot_size").val(fifth_foot_size);
      $(".50_10_sleeve_cost").val(fifth_sleeve_cost);
      $(".50_10_factor").val(fifth_factor);
      $(".50_10_sleeve_total_cost").val(fifth_cost);
      $(".50_10_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "60*5") {
      let fifth_bbr_weight = fifth_foot_size * 0.85;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".60_5_foot_size").val(fifth_foot_size);
      $(".60_5_sleeve_cost").val(fifth_sleeve_cost);
      $(".60_5_factor").val(fifth_factor);
      $(".60_5_sleeve_total_cost").val(fifth_cost);
      $(".60_5_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "60*10") {
      let fifth_bbr_weight = fifth_foot_size * 1.7;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".60_10_foot_size").val(fifth_foot_size);
      $(".60_10_sleeve_cost").val(fifth_sleeve_cost);
      $(".60_10_factor").val(fifth_factor);
      $(".60_10_sleeve_total_cost").val(fifth_cost);
      $(".60_10_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "80*5") {
      let fifth_bbr_weight = fifth_foot_size * 1.1;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".80_5_foot_size").val(fifth_foot_size);
      $(".80_5_sleeve_cost").val(fifth_sleeve_cost);
      $(".80_5_factor").val(fifth_factor);
      $(".80_5_sleeve_total_cost").val(fifth_cost);
      $(".80_5_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "80*10") {
      let fifth_bbr_weight = fifth_foot_size * 2.2;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".80_10_foot_size").val(fifth_foot_size);
      $(".80_10_sleeve_cost").val(fifth_sleeve_cost);
      $(".80_10_factor").val(fifth_factor);
      $(".80_10_sleeve_total_cost").val(fifth_cost);
      $(".80_10_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "100*5") {
      let fifth_bbr_weight = fifth_foot_size * 1.4;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".100_5_foot_size").val(fifth_foot_size);
      $(".100_5_sleeve_cost").val(fifth_sleeve_cost);
      $(".100_5_factor").val(fifth_factor);
      $(".100_5_sleeve_total_cost").val(fifth_cost);
      $(".100_5_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "100*10") {
      let fifth_bbr_weight = fifth_foot_size * 2.8;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".100_10_foot_size").val(fifth_foot_size);
      $(".100_10_sleeve_cost").val(fifth_sleeve_cost);
      $(".100_10_factor").val(fifth_factor);
      $(".100_10_sleeve_total_cost").val(fifth_cost);
      $(".100_10_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "120*5") {
      let fifth_bbr_weight = fifth_foot_size * 1.67;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".120_5_foot_size").val(fifth_foot_size);
      $(".120_5_sleeve_cost").val(fifth_sleeve_cost);
      $(".120_5_factor").val(fifth_factor);
      $(".120_5_sleeve_total_cost").val(fifth_cost);
      $(".120_5_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "120*10") {
      let fifth_bbr_weight = fifth_foot_size * 3.3;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".120_10_foot_size").val(fifth_foot_size);
      $(".120_10_sleeve_cost").val(fifth_sleeve_cost);
      $(".120_10_factor").val(fifth_factor);
      $(".120_10_sleeve_total_cost").val(fifth_cost);
      $(".120_10_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    } else if (dimesnsion == "150*10") {
      let fifth_bbr_weight = fifth_foot_size * 4.2;
      $(".fifth_bbr_weight").val(fifth_bbr_weight);
      $(".150_10_foot_size").val(fifth_foot_size);
      $(".150_10_sleeve_cost").val(fifth_sleeve_cost);
      $(".150_10_factor").val(fifth_factor);
      $(".150_10_sleeve_total_cost").val(fifth_cost);
      $(".150_10_bbr_weight").val(fifth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/of_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:fifth_foot_size,
          first_sleeve_cost:fifth_sleeve_cost,
          first_factor:fifth_factor,
          first_cost:fifth_cost,
          first_bbr_weight:fifth_bbr_weight
        }
      });
    }
    $("#twenty_one").css("background-color", "rgb(13, 211, 102)");
  } else {
    Swal.fire("Please Enter All Values First");
  }
});

$(document).on("click", "#sixth_cost", function () {
  event.preventDefault();
  let pannel_costing_id = $(".pannel_costing_id").val();
  let dimesnsion = document.getElementById("sixth_dms").value;
  let sixth_foot_size = document.getElementById("sixth_foot_size").value;
  let sixth_sleeve_cost = document.getElementById("sixth_sleeve_cost").value;
  let sixth_factor = document.getElementById("sixth_factor").value;
  if (sixth_foot_size != "" && sixth_sleeve_cost != "" && sixth_factor != "") {
    let sixth_cost = sixth_foot_size * sixth_sleeve_cost * sixth_factor;
    $(".sixth_cost").val(sixth_cost);

    if (dimesnsion == "20*5") {
      let sixth_bbr_weight = sixth_foot_size * 0.3;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);

      $(".20_5_foot_size").val(sixth_foot_size);
      $(".20_5_sleeve_cost").val(sixth_sleeve_cost);
      $(".20_5_factor").val(sixth_factor);
      $(".20_5_sleeve_total_cost").val(sixth_cost);
      $(".20_5_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/t_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "25*5") {
      let sixth_bbr_weight = sixth_foot_size * 0.4;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);

      $(".25_5_foot_size").val(sixth_foot_size);
      $(".25_5_sleeve_cost").val(sixth_sleeve_cost);
      $(".25_5_factor").val(sixth_factor);
      $(".25_5_sleeve_total_cost").val(sixth_cost);
      $(".25_5_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "25*10") {
      let sixth_bbr_weight = sixth_foot_size * 0.7;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);

      $(".25_10_foot_size").val(sixth_foot_size);
      $(".25_10_sleeve_cost").val(sixth_sleeve_cost);
      $(".25_10_factor").val(sixth_factor);
      $(".25_10_sleeve_total_cost").val(sixth_cost);
      $(".25_10_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/tf_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "30*5") {
      let sixth_bbr_weight = sixth_foot_size * 0.45;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".30_5_foot_size").val(sixth_foot_size);
      $(".30_5_sleeve_cost").val(sixth_sleeve_cost);
      $(".30_5_factor").val(sixth_factor);
      $(".30_5_sleeve_total_cost").val(sixth_cost);
      $(".30_5_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "30*10") {
      let sixth_bbr_weight = sixth_foot_size * 0.85;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".30_10_foot_size").val(sixth_foot_size);
      $(".30_10_sleeve_cost").val(sixth_sleeve_cost);
      $(".30_10_factor").val(sixth_factor);
      $(".30_10_sleeve_total_cost").val(sixth_cost);
      $(".30_10_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ty_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "40*5") {
      let sixth_bbr_weight = sixth_foot_size * 0.6;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".40_5_foot_size").val(sixth_foot_size);
      $(".40_5_sleeve_cost").val(sixth_sleeve_cost);
      $(".40_5_factor").val(sixth_factor);
      $(".40_5_sleeve_total_cost").val(sixth_cost);
      $(".40_5_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "40*10") {
      let sixth_bbr_weight = sixth_foot_size * 1.1;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".40_10_foot_size").val(sixth_foot_size);
      $(".40_10_sleeve_cost").val(sixth_sleeve_cost);
      $(".40_10_factor").val(sixth_factor);
      $(".40_10_sleeve_total_cost").val(sixth_cost);
      $(".40_10_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/f_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "50*5") {
      let sixth_bbr_weight = sixth_foot_size * 0.7;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".50_5_foot_size").val(sixth_foot_size);
      $(".50_5_sleeve_cost").val(sixth_sleeve_cost);
      $(".50_5_factor").val(sixth_factor);
      $(".50_5_sleeve_total_cost").val(sixth_cost);
      $(".50_5_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "50*10") {
      let sixth_bbr_weight = sixth_foot_size * 1.4;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".50_10_foot_size").val(sixth_foot_size);
      $(".50_10_sleeve_cost").val(sixth_sleeve_cost);
      $(".50_10_factor").val(sixth_factor);
      $(".50_10_sleeve_total_cost").val(sixth_cost);
      $(".50_10_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/fy_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "60*5") {
      let sixth_bbr_weight = sixth_foot_size * 0.85;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".60_5_foot_size").val(sixth_foot_size);
      $(".60_5_sleeve_cost").val(sixth_sleeve_cost);
      $(".60_5_factor").val(sixth_factor);
      $(".60_5_sleeve_total_cost").val(sixth_cost);
      $(".60_5_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "60*10") {
      let sixth_bbr_weight = sixth_foot_size * 1.7;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".60_10_foot_size").val(sixth_foot_size);
      $(".60_10_sleeve_cost").val(sixth_sleeve_cost);
      $(".60_10_factor").val(sixth_factor);
      $(".60_10_sleeve_total_cost").val(sixth_cost);
      $(".60_10_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/s_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "80*5") {
      let sixth_bbr_weight = sixth_foot_size * 1.1;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".80_5_foot_size").val(sixth_foot_size);
      $(".80_5_sleeve_cost").val(sixth_sleeve_cost);
      $(".80_5_factor").val(sixth_factor);
      $(".80_5_sleeve_total_cost").val(sixth_cost);
      $(".80_5_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "80*10") {
      let sixth_bbr_weight = sixth_foot_size * 2.2;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".80_10_foot_size").val(sixth_foot_size);
      $(".80_10_sleeve_cost").val(sixth_sleeve_cost);
      $(".80_10_factor").val(sixth_factor);
      $(".80_10_sleeve_total_cost").val(sixth_cost);
      $(".80_10_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/e_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "100*5") {
      let sixth_bbr_weight = sixth_foot_size * 1.4;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".100_5_foot_size").val(sixth_foot_size);
      $(".100_5_sleeve_cost").val(sixth_sleeve_cost);
      $(".100_5_factor").val(sixth_factor);
      $(".100_5_sleeve_total_cost").val(sixth_cost);
      $(".100_5_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "100*10") {
      let sixth_bbr_weight = sixth_foot_size * 2.8;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".100_10_foot_size").val(sixth_foot_size);
      $(".100_10_sleeve_cost").val(sixth_sleeve_cost);
      $(".100_10_factor").val(sixth_factor);
      $(".100_10_sleeve_total_cost").val(sixth_cost);
      $(".100_10_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/h_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "120*5") {
      let sixth_bbr_weight = sixth_foot_size * 1.67;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".120_5_foot_size").val(sixth_foot_size);
      $(".120_5_sleeve_cost").val(sixth_sleeve_cost);
      $(".120_5_factor").val(sixth_factor);
      $(".120_5_sleeve_total_cost").val(sixth_cost);
      $(".120_5_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_5.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "120*10") {
      let sixth_bbr_weight = sixth_foot_size * 3.3;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".120_10_foot_size").val(sixth_foot_size);
      $(".120_10_sleeve_cost").val(sixth_sleeve_cost);
      $(".120_10_factor").val(sixth_factor);
      $(".120_10_sleeve_total_cost").val(sixth_cost);
      $(".120_10_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/ot_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    } else if (dimesnsion == "150*10") {
      let sixth_bbr_weight = sixth_foot_size * 4.2;
      $(".sixth_bbr_weight").val(sixth_bbr_weight);
      $(".150_10_foot_size").val(sixth_foot_size);
      $(".150_10_sleeve_cost").val(sixth_sleeve_cost);
      $(".150_10_factor").val(sixth_factor);
      $(".150_10_sleeve_total_cost").val(sixth_cost);
      $(".150_10_bbr_weight").val(sixth_bbr_weight);
      $.ajax({
        url: "cost/bus_bar/of_10.php",
        method: "POST",
        data: {
          pannel_costing_id: pannel_costing_id,
          first_foot_size:sixth_foot_size,
          first_sleeve_cost:sixth_sleeve_cost,
          first_factor:sixth_factor,
          first_cost:sixth_cost,
          first_bbr_weight:sixth_bbr_weight
        }
      });
    }
  } else {
    Swal.fire("Please Enter All Values First");
  }
});


