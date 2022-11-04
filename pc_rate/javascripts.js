
  document.getElementById('ms_sheet').onchange = function() {
    ms_sheet=$("#ms_sheet").val();
    $.ajax({
        url: "assets/ms_sheet.php",
        method: "POST",
        data: {
          ms_sheet:ms_sheet,
        },
        success: function (data) {
            setTimeout(function() {
              $("#ms_sheet").css("border","green 2px solid"); // change it back after ...
          }, 1000);
        },
      });
    
  };

  document.getElementById('ss_sheet').onchange = function() {
    ss_sheet=$("#ss_sheet").val();
    $.ajax({
      url: "assets/ss_sheet.php",
      method: "POST",
      data: {
        ss_sheet:ss_sheet,
      },
      success: function (data) {
          setTimeout(function() {
            $("#ss_sheet").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('gi_sheet').onchange = function() {
    gi_sheet=$("#gi_sheet").val();
    $.ajax({
      url: "assets/gi_sheet.php",
      method: "POST",
      data: {
        gi_sheet:gi_sheet,
      },
      success: function (data) {
          setTimeout(function() {
            $("#gi_sheet").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('h_7032').onchange = function() {
    h_7032=$("#h_7032").val();
    $.ajax({
      url: "assets/h_7032.php",
      method: "POST",
      data: {
        h_7032:h_7032,
      },
      success: function (data) {
          setTimeout(function() {
            $("#h_7032").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('h_7035').onchange = function() {
    h_7035=$("#h_7035").val();
    $.ajax({
      url: "assets/h_7035.php",
      method: "POST",
      data: {
        h_7035:h_7035,
      },
      success: function (data) {
          setTimeout(function() {
            $("#h_7035").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('tf_7032').onchange = function() {
    tf_7032=$("#tf_7032").val();
    $.ajax({
      url: "assets/tf_7032.php",
      method: "POST",
      data: {
        tf_7032:tf_7032,
      },
      success: function (data) {
          setTimeout(function() {
            $("#tf_7032").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('tf_7035').onchange = function() {
    tf_7035=$("#tf_7035").val();
    $.ajax({
      url: "assets/tf_7035.php",
      method: "POST",
      data: {
        tf_7035:tf_7035,
      },
      success: function (data) {
          setTimeout(function() {
            $("#tf_7035").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('thf_7032').onchange = function() {
    thf_7032=$("#thf_7032").val();
    $.ajax({
      url: "assets/thf_7032.php",
      method: "POST",
      data: {
        thf_7032:thf_7032,
      },
      success: function (data) {
          setTimeout(function() {
            $("#thf_7032").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('thf_7035').onchange = function() {
    thf_7035=$("#thf_7035").val();
    $.ajax({
      url: "assets/thf_7035.php",
      method: "POST",
      data: {
        thf_7035:thf_7035,
      },
      success: function (data) {
          setTimeout(function() {
            $("#thf_7035").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('f_7032').onchange = function() {
    f_7032=$("#f_7032").val();
    $.ajax({
      url: "assets/f_7032.php",
      method: "POST",
      data: {
        f_7032:f_7032,
      },
      success: function (data) {
          setTimeout(function() {
            $("#f_7032").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('f_7035').onchange = function() {
    f_7035=$("#f_7035").val();
    $.ajax({
      url: "assets/f_7035.php",
      method: "POST",
      data: {
        f_7035:f_7035,
      },
      success: function (data) {
          setTimeout(function() {
            $("#f_7035").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('s_7032').onchange = function() {
    s_7032=$("#s_7032").val();
    $.ajax({
      url: "assets/s_7032.php",
      method: "POST",
      data: {
        s_7032:s_7032,
      },
      success: function (data) {
          setTimeout(function() {
            $("#s_7032").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('s_7035').onchange = function() {
    s_7035=$("#s_7035").val();
    $.ajax({
      url: "assets/s_7035.php",
      method: "POST",
      data: {
        s_7035:s_7035,
      },
      success: function (data) {
          setTimeout(function() {
            $("#s_7035").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('n_7032').onchange = function() {
    n_7032=$("#n_7032").val();
    $.ajax({
      url: "assets/n_7032.php",
      method: "POST",
      data: {
        n_7032:n_7032,
      },
      success: function (data) {
          setTimeout(function() {
            $("#n_7032").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('n_7035').onchange = function() {
    n_7035=$("#n_7035").val();
    $.ajax({
      url: "assets/n_7035.php",
      method: "POST",
      data: {
        n_7035:n_7035,
      },
      success: function (data) {
          setTimeout(function() {
            $("#n_7035").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('hl_030').onchange = function() {
    hl_030=$("#hl_030").val();
    $.ajax({
      url: "assets/hl_030.php",
      method: "POST",
      data: {
        hl_030:hl_030,
      },
      success: function (data) {
          setTimeout(function() {
            $("#hl_030").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('hl_027').onchange = function() {
    hl_027=$("#hl_027").val();
    $.ajax({
      url: "assets/hl_027.php",
      method: "POST",
      data: {
        hl_027:hl_027,
      },
      success: function (data) {
          setTimeout(function() {
            $("#hl_027").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('hl_056').onchange = function() {
    hl_056=$("#hl_056").val();
    $.ajax({
      url: "assets/hl_056.php",
      method: "POST",
      data: {
        hl_056:hl_056,
      },
      success: function (data) {
          setTimeout(function() {
            $("#hl_056").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('hl_051').onchange = function() {
    hl_051=$("#hl_051").val();
    $.ajax({
      url: "assets/hl_051.php",
      method: "POST",
      data: {
        hl_051:hl_051,
      },
      success: function (data) {
          setTimeout(function() {
            $("#hl_051").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('ms_480').onchange = function() {
    ms_480=$("#ms_480").val();
    $.ajax({
      url: "assets/ms_480.php",
      method: "POST",
      data: {
        ms_480:ms_480,
      },
      success: function (data) {
          setTimeout(function() {
            $("#ms_480").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('ms_408').onchange = function() {
    ms_408=$("#ms_408").val();
    $.ajax({
      url: "assets/ms_408.php",
      method: "POST",
      data: {
        ms_408:ms_408,
      },
      success: function (data) {
          setTimeout(function() {
            $("#ms_408").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('bnl_22').onchange = function() {
    bnl_22=$("#bnl_22").val();
    $.ajax({
      url: "assets/bnl_22.php",
      method: "POST",
      data: {
        bnl_22:bnl_22,
      },
      success: function (data) {
          setTimeout(function() {
            $("#bnl_22").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('pl_130').onchange = function() {
    pl_130=$("#pl_130").val();
    $.ajax({
      url: "assets/pl_130.php",
      method: "POST",
      data: {
        pl_130:pl_130,
      },
      success: function (data) {
          setTimeout(function() {
            $("#pl_130").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('pl_150').onchange = function() {
    pl_150=$("#pl_150").val();
    $.ajax({
      url: "assets/pl_150.php",
      method: "POST",
      data: {
        pl_150:pl_150,
      },
      success: function (data) {
          setTimeout(function() {
            $("#pl_150").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('acrylic_sheet').onchange = function() {
    acrylic_sheet=$("#acrylic_sheet").val();
    $.ajax({
      url: "assets/acrylic_sheet.php",
      method: "POST",
      data: {
        acrylic_sheet:acrylic_sheet,
      },
      success: function (data) {
          setTimeout(function() {
            $("#acrylic_sheet").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('gas_kit').onchange = function() {
    gas_kit=$("#gas_kit").val();
    $.ajax({
      url: "assets/gas_kit.php",
      method: "POST",
      data: {
        gas_kit:gas_kit,
      },
      success: function (data) {
          setTimeout(function() {
            $("#gas_kit").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('i_bolt').onchange = function() {
    i_bolt=$("#i_bolt").val();
    $.ajax({
      url: "assets/i_bolt.php",
      method: "POST",
      data: {
        i_bolt:i_bolt,
      },
      success: function (data) {
          setTimeout(function() {
            $("#i_bolt").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('bus_bar').onchange = function() {
    bus_bar=$("#bus_bar").val();
    $.ajax({
      url: "assets/bus_bar.php",
      method: "POST",
      data: {
        bus_bar:bus_bar,
      },
      success: function (data) {
          setTimeout(function() {
            $("#bus_bar").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('tf_tf').onchange = function() {
    tf_tf=$("#tf_tf").val();
    $.ajax({
      url: "assets/tf_tf.php",
      method: "POST",
      data: {
        tf_tf:tf_tf,
      },
      success: function (data) {
          setTimeout(function() {
            $("#tf_tf").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('tf_f').onchange = function() {
    tf_f=$("#tf_f").val();
    $.ajax({
      url: "assets/tf_f.php",
      method: "POST",
      data: {
        tf_f:tf_f,
      },
      success: function (data) {
          setTimeout(function() {
            $("#tf_f").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('tt_tt').onchange = function() {
    tt_tt=$("#tt_tt").val();
    $.ajax({
      url: "assets/tt_tt.php",
      method: "POST",
      data: {
        tt_tt:tt_tt,
      },
      success: function (data) {
          setTimeout(function() {
            $("#tt_tt").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('f_f').onchange = function() {
    f_f=$("#f_f").val();
    $.ajax({
      url: "assets/f_f.php",
      method: "POST",
      data: {
        f_f:f_f,
      },
      success: function (data) {
          setTimeout(function() {
            $("#f_f").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('f_s').onchange = function() {
    f_s=$("#f_s").val();
    $.ajax({
      url: "assets/f_s.php",
      method: "POST",
      data: {
        f_s:f_s,
      },
      success: function (data) {
          setTimeout(function() {
            $("#f_s").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('s_f').onchange = function() {
    s_f=$("#s_f").val();
    $.ajax({
      url: "assets/s_f.php",
      method: "POST",
      data: {
        s_f:s_f,
      },
      success: function (data) {
          setTimeout(function() {
            $("#s_f").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('s_s').onchange = function() {
    s_s=$("#s_s").val();
    $.ajax({
      url: "assets/s_s.php",
      method: "POST",
      data: {
        s_s:s_s,
      },
      success: function (data) {
          setTimeout(function() {
            $("#s_s").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('e_e').onchange = function() {
    e_e=$("#e_e").val();
    $.ajax({
      url: "assets/e_e.php",
      method: "POST",
      data: {
        e_e:e_e,
      },
      success: function (data) {
          setTimeout(function() {
            $("#e_e").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };

  document.getElementById('h_h').onchange = function() {
    h_h=$("#h_h").val();
    $.ajax({
      url: "assets/h_h.php",
      method: "POST",
      data: {
        h_h:h_h,
      },
      success: function (data) {
          setTimeout(function() {
            $("#h_h").css("border","green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  };