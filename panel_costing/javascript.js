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
  $("#five_bus_bar").hide();
  $("#six_bus_bar").hide();
  $("#seven_bus_bar").hide();
  $("#eight_bus_bar").hide();
  $("#nine_bus_bar").hide();
  $("#ten_bus_bar").hide();
  $("#eleven_bus_bar").hide();
  $("#twelve_bus_bar").hide();
  $("#thirteen_bus_bar").hide();
  $("#fourteen_bus_bar").hide();
  $("#fifteen_bus_bar").hide();

});

var buttonElem = document.querySelector('.button');

buttonElem.addEventListener("click", function () {
  buttonElem.classList.add('spinning');

  setTimeout(
    function () {
      buttonElem.classList.remove('spinning');

    }, 3000);
}, false);

document.getElementById('pc_h').onchange = function () {
  var pc_h = $("#pc_h").val();
  $.ajax({
    url: "cost/pc_h.php",
    method: "POST",
    data: {
      pc_h: pc_h
    },
    success: function (data) {
      $(".pc_id").val(data);

      setTimeout(function () {
        $("#pc_h").css("border", "green 2px solid"); // change it back after ...
      }, 1000);
    },
  });
};

document.getElementById('pc_w').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var pc_w = $("#pc_w").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/pc_w.php",
      method: "POST",
      data: {
        pc_w: pc_w,
        pc_id: pc_id
      },
      success: function (data) {

        buttonElem.addEventListener("click", function () {
          buttonElem.classList.add('spinning');

          setTimeout(
            function () {
              buttonElem.classList.remove('spinning');

            }, 3000);
        }, false);
        setTimeout(function () {
          $("#pc_w").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('pc_d').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var pc_d = $("#pc_d").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/pc_d.php",
      method: "POST",
      data: {
        pc_d: pc_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#pc_d").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('pc_type').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var pc_type = $("#pc_type").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/pc_type.php",
      method: "POST",
      data: {
        pc_type: pc_type,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#pc_type").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('conopy').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var conopy = $("#conopy").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/conopy.php",
      method: "POST",
      data: {
        conopy: conopy,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#conopy").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door1_h').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door1_h = $("#door1_h").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door1_h.php",
      method: "POST",
      data: {
        door1_h: door1_h,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door1_h").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door1_w').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door1_w = $("#door1_w").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door1_w.php",
      method: "POST",
      data: {
        door1_w: door1_w,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door1_w").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door1_d').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door1_d = $("#door1_d").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door1_d.php",
      method: "POST",
      data: {
        door1_d: door1_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door1_d").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('door1_cp').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door1_cp = $("#door1_cp").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door1_cp.php",
      method: "POST",
      data: {
        door1_cp: door1_cp,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door1_cp").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door2_h').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door2_h = $("#door2_h").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door2_h.php",
      method: "POST",
      data: {
        door2_h: door2_h,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door2_h").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door2_w').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door2_w = $("#door2_w").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door2_w.php",
      method: "POST",
      data: {
        door2_w: door2_w,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door2_w").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door2_d').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door2_d = $("#door2_d").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door2_d.php",
      method: "POST",
      data: {
        door2_d: door2_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door2_d").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door2_cp').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door2_cp = $("#door2_cp").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door2_cp.php",
      method: "POST",
      data: {
        door2_cp: door2_cp,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door2_cp").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door3_h').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door3_h = $("#door3_h").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door3_h.php",
      method: "POST",
      data: {
        door3_h: door3_h,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door3_h").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door3_w').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door3_w = $("#door3_w").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door3_w.php",
      method: "POST",
      data: {
        door3_w: door3_w,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door3_w").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door3_d').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door3_d = $("#door3_d").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door3_d.php",
      method: "POST",
      data: {
        door3_d: door3_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door3_d").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door3_cp').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door3_cp = $("#door3_cp").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door3_cp.php",
      method: "POST",
      data: {
        door3_cp: door3_cp,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door3_cp").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('door4_h').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door4_h = $("#door4_h").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door4_h.php",
      method: "POST",
      data: {
        door4_h: door4_h,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door4_h").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door4_w').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door4_w = $("#door4_w").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door4_w.php",
      method: "POST",
      data: {
        door4_w: door4_w,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door4_w").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door4_d').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door4_d = $("#door4_d").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door4_d.php",
      method: "POST",
      data: {
        door4_d: door4_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door4_d").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door4_cp').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door4_cp = $("#door4_cp").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door4_cp.php",
      method: "POST",
      data: {
        door4_cp: door4_cp,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door4_cp").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door5_h').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door5_h = $("#door5_h").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door5_h.php",
      method: "POST",
      data: {
        door5_h: door5_h,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door5_h").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door5_w').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door5_w = $("#door5_w").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door5_w.php",
      method: "POST",
      data: {
        door5_w: door5_w,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door5_w").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door5_d').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door5_d = $("#door5_d").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door5_d.php",
      method: "POST",
      data: {
        door5_d: door5_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door5_d").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door5_cp').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door5_cp = $("#door5_cp").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door5_cp.php",
      method: "POST",
      data: {
        door5_cp: door5_cp,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door5_cp").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door6_h').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door6_h = $("#door6_h").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door6_h.php",
      method: "POST",
      data: {
        door6_h: door6_h,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door6_h").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door6_w').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door6_w = $("#door6_w").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door6_w.php",
      method: "POST",
      data: {
        door6_w: door6_w,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door6_w").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door6_d').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door6_d = $("#door6_d").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door6_d.php",
      method: "POST",
      data: {
        door6_d: door6_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door6_d").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door6_cp').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door6_cp = $("#door6_cp").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door6_cp.php",
      method: "POST",
      data: {
        door6_cp: door6_cp,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door6_cp").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door7_h').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door7_h = $("#door7_h").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door7_h.php",
      method: "POST",
      data: {
        door7_h: door7_h,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door7_h").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door7_w').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door7_w = $("#door7_w").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door7_w.php",
      method: "POST",
      data: {
        door7_w: door7_w,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door7_w").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door7_d').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door7_d = $("#door7_d").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door7_d.php",
      method: "POST",
      data: {
        door7_d: door7_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door7_d").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door7_cp').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door7_cp = $("#door7_cp").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door7_cp.php",
      method: "POST",
      data: {
        door7_cp: door7_cp,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door7_cp").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door8_h').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_h = $("#door8_h").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door8_h.php",
      method: "POST",
      data: {
        door8_h: door8_h,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door8_h").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door8_w').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_w = $("#door8_w").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door8_w.php",
      method: "POST",
      data: {
        door8_w: door8_w,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door8_w").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door8_d').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#door8_d").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door/door8_d.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door8_d").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('door8_cp').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_cp = $("#door8_cp").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/door8_cp.php",
      method: "POST",
      data: {
        door8_cp: door8_cp,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#door8_cp").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('sheet_selection').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#sheet_selection").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/sheet_selection.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#sheet_selection").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty').onchange = function () {

  dimension = $("#bb_dimension").val();
  qty = $("#busbar_qty").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight").val(weight);
  $("#busbar_sleeve").val(sleeve);



  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty").val();
  var weight = $("#busbar_weight").val();
  var sleeve = $("#busbar_sleeve").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_two').onchange = function () {

  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_two").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_two.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_two").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    t
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_two').onchange = function () {

  dimension = $("#bb_dimension_two").val();
  qty = $("#busbar_qty_two").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_two").val(weight);
  $("#busbar_sleeve_two").val(sleeve);



  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_two").val();
  var weight = $("#busbar_weight_two").val();
  var sleeve = $("#busbar_sleeve_two").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_two.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_two").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_three').onchange = function () {

  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_three").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_three.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_three").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_three').onchange = function () {

  dimension = $("#bb_dimension_three").val();
  qty = $("#busbar_qty_three").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_three").val(weight);
  $("#busbar_sleeve_three").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_three").val();
  var weight = $("#busbar_weight_three").val();
  var sleeve = $("#busbar_sleeve_three").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_three.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_three").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_four').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_four").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_four.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_four").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_four').onchange = function () {

  dimension = $("#bb_dimension_four").val();
  qty = $("#busbar_qty_four").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_four").val(weight);
  $("#busbar_sleeve_four").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_four").val();
  var weight = $("#busbar_weight_four").val();
  var sleeve = $("#busbar_sleeve_four").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_four.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_four").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};



document.getElementById('bb_dimension_five').onchange = function () {

  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_five").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_five.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_five").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_five').onchange = function () {

  dimension = $("#bb_dimension_five").val();
  qty = $("#busbar_qty_five").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_five").val(weight);
  $("#busbar_sleeve_five").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_five").val();
  var weight = $("#busbar_weight_five").val();
  var sleeve = $("#busbar_sleeve_five").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_five.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_five").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_six').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_six").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_six.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_six").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_six').onchange = function () {

  dimension = $("#bb_dimension_six").val();
  qty = $("#busbar_qty_six").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_six").val(weight);
  $("#busbar_sleeve_six").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_six").val();
  var weight = $("#busbar_weight_six").val();
  var sleeve = $("#busbar_sleeve_six").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_six.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_six").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_seven').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_seven").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_seven.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_seven").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_seven').onchange = function () {

  dimension = $("#bb_dimension_seven").val();
  qty = $("#busbar_qty_seven").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_seven").val(weight);
  $("#busbar_sleeve_seven").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_seven").val();
  var weight = $("#busbar_weight_seven").val();
  var sleeve = $("#busbar_sleeve_seven").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_seven.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_seven").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_eight').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_eight").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_eight.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_eight").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_eight').onchange = function () {

  dimension = $("#bb_dimension_eight").val();
  qty = $("#busbar_qty_eight").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_eight").val(weight);
  $("#busbar_sleeve_eight").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_eight").val();
  var weight = $("#busbar_weight_eight").val();
  var sleeve = $("#busbar_sleeve_eight").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_eight.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_eight").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_nine').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_eight").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_nine.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_nine").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_nine').onchange = function () {

  dimension = $("#bb_dimension_nine").val();
  qty = $("#busbar_qty_nine").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_nine").val(weight);
  $("#busbar_sleeve_nine").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_nine").val();
  var weight = $("#busbar_weight_nine").val();
  var sleeve = $("#busbar_sleeve_nine").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_nine.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_nine").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_ten').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_ten").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_ten.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_ten").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_ten').onchange = function () {

  dimension = $("#bb_dimension_ten").val();
  qty = $("#busbar_qty_ten").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_ten").val(weight);
  $("#busbar_sleeve_ten").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_ten").val();
  var weight = $("#busbar_weight_ten").val();
  var sleeve = $("#busbar_sleeve_ten").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_ten.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_ten").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_eleven').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_eleven").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_eleven.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_eleven").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_eleven').onchange = function () {

  dimension = $("#bb_dimension_eleven").val();
  qty = $("#busbar_qty_eleven").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_eleven").val(weight);
  $("#busbar_sleeve_eleven").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_eleven").val();
  var weight = $("#busbar_weight_eleven").val();
  var sleeve = $("#busbar_sleeve_eleven").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_eleven.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_eleven").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_twelve').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_twelve").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_twelve.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_twelve").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_twelve').onchange = function () {

  dimension = $("#bb_dimension_twelve").val();
  qty = $("#busbar_qty_twelve").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_twelve").val(weight);
  $("#busbar_sleeve_twelve").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_twelve").val();
  var weight = $("#busbar_weight_twelve").val();
  var sleeve = $("#busbar_sleeve_twelve").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_twelve.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_twelve").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_thirteen').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_thirteen").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_thirteen.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_thirteen").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_thirteen').onchange = function () {

  dimension = $("#bb_dimension_thirteen").val();
  qty = $("#busbar_qty_thirteen").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_thirteen").val(weight);
  $("#busbar_sleeve_thirteen").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_thirteen").val();
  var weight = $("#busbar_weight_thirteen").val();
  var sleeve = $("#busbar_sleeve_thirteen").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_thirteen.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_thirteen").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_fourteen').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_fourteen").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_fourteen.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_fourteen").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_fourteen').onchange = function () {

  dimension = $("#bb_dimension_fourteen").val();
  qty = $("#busbar_qty_fourteen").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_fourteen").val(weight);
  $("#busbar_sleeve_fourteen").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_fourteen").val();
  var weight = $("#busbar_weight_fourteen").val();
  var sleeve = $("#busbar_sleeve_fourteen").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_fourteen.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_fourteen").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('bb_dimension_fifteen').onchange = function () {


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#bb_dimension_fifteen").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/bb_dimension_fifteen.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#bb_dimension_fifteen").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('busbar_qty_fifteen').onchange = function () {

  dimension = $("#bb_dimension_fifteen").val();
  qty = $("#busbar_qty_fifteen").val();
  var weight = 0;
  var sleeve = 0;
  if (dimension == "20*5") { weight = qty * 0.3; sleeve = qty * 43 * 1.5; }
  else if (dimension == "25*5") { weight = qty * 0.4; sleeve = qty * 48 * 1.5; }
  else if (dimension == "25*10") { weight = qty * 0.7; sleeve = qty * 55 * 1.5; }
  else if (dimension == "30*5") { weight = qty * 0.45; sleeve = qty * 60 * 1.5; }
  else if (dimension == "30*10") { weight = qty * 0.85; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*5") { weight = qty * 0.6; sleeve = qty * 65 * 1.5; }
  else if (dimension == "40*10") { weight = qty * 1.1; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*5") { weight = qty * 0.7; sleeve = qty * 75 * 1.5; }
  else if (dimension == "50*10") { weight = qty * 1.4; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*5") { weight = qty * 0.85; sleeve = qty * 85 * 1.5; }
  else if (dimension == "60*10") { weight = qty * 1.7; sleeve = qty * 95 * 1.5; }
  else if (dimension == "80*5") { weight = qty * 1.1; sleeve = qty * 110 * 1.5; }
  else if (dimension == "80*10") { weight = qty * 2.2; sleeve = qty * 115 * 1.5; }
  else if (dimension == "100*5") { weight = qty * 1.4; sleeve = qty * 125 * 1.5; }
  else if (dimension == "100*10") { weight = qty * 2.8; sleeve = qty * 135 * 1.5; }
  else if (dimension == "120*5") { weight = qty * 1.67; sleeve = qty * 145 * 1.5; }
  else if (dimension == "120*10") { weight = qty * 3.3; sleeve = qty * 155 * 1.5; }
  else if (dimension == "150*10") { weight = qty * 4.2; sleeve = qty * 185 * 1.5; }
  else {
    Swal.fire("Please select dimension first.")
  }
  $("#busbar_weight_fifteen").val(weight);
  $("#busbar_sleeve_fifteen").val(sleeve);


  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#busbar_qty_fifteen").val();
  var weight = $("#busbar_weight_fifteen").val();
  var sleeve = $("#busbar_sleeve_fifteen").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/dimensions/busbar_qty_fifteen.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id,
        weight: weight,
        sleeve: sleeve
      },
      success: function (data) {
        setTimeout(function () {
          $("#busbar_qty_fifteen").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('paint_cost_model').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#paint_cost_model").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/paint_cost.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#paint_cost_model").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('ac_qty').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#ac_qty").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/ac_qty.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#ac_qty").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('gk_qty').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#gk_qty").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/gk_qty.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#gk_qty").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('ibolt_qty').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#ibolt_qty").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/ibolt_qty.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#ibolt_qty").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('hinges_model').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#hinges_model").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/hinges_model.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#hinges_model").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('hinges_qty').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#hinges_qty").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/hinges_qty.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#hinges_qty").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('lock_model').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#lock_model").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/lock_model.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#lock_model").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};


document.getElementById('lock_qty').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#lock_qty").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/lock_qty.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#lock_qty").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};
document.getElementById('cd_model').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#cd_model").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/cd_model.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#cd_model").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};
document.getElementById('cd_qty').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#cd_qty").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/cd_qty.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#cd_qty").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};
document.getElementById('wiring_cost').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#wiring_cost").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/wiring_cost.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#wiring_cost").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};
document.getElementById('labour').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#labour").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/labour.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#labour").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};
document.getElementById('misc_exp').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#misc_exp").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/misc_exp.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#misc_exp").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('rent').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#rent").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/rent.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#rent").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('Increase_percent_14').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#Increase_percent_14").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/Increase_percent_14.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#Increase_percent_14").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('Increase_percent_16').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#Increase_percent_16").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/Increase_percent_16.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#Increase_percent_16").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('Increase_percent_18').onchange = function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  var door8_d = $("#Increase_percent_18").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/Increase_percent_18.php",
      method: "POST",
      data: {
        door8_d: door8_d,
        pc_id: pc_id
      },
      success: function (data) {
        setTimeout(function () {
          $("#Increase_percent_18").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  } else {
    Swal.fire("Please Enter Height Value First");
  }
};

document.getElementById('sheet_sheet_cd').onchange = function () {
  var sheet_sheet_cd = $("#sheet_sheet_cd").val();
  var pc_id = $(".pc_id").val();
  if (pc_h != "") {
    $.ajax({
      url: "cost/sheet_sheet_cd.php",
      method: "POST",
      data: {
        pc_id: pc_id,
        sheet_sheet_cd:sheet_sheet_cd
      },
      success: function (data) {
        setTimeout(function () {
          $("#sheet_sheet_cd").css("border", "green 2px solid"); // change it back after ...
        }, 1000);
      },
    });
  }
  else {
    Swal.fire("It seems you have't calculated or stored all of values");
  }
};


$(document).on("click", "#calculateButton", function () {
  var pc_h = $("#pc_h").val();
  var pc_id = $(".pc_id").val();
  if (pc_h != "") {
    let model = document.getElementById("hinges_model").value;
    let qty = document.getElementById("hinges_qty").value;
    $.ajax({
      url: "cost/calculate/hinges.php",
      method: "POST",
      data: {
        model: model,
        qty: qty,
        pc_id: pc_id
      },
      success: function (data) {
        if (data) {
          $("#hinges_cost").val(data);
        }
      },
    });

    var pc_h = $("#pc_h").val();
    var pc_w = $("#pc_w").val();
    var pc_d = $("#pc_d").val();
    var paint_cost_model = $("#paint_cost_model").val();
    $.ajax({
      url: "cost/calculate/paint_cost.php",
      method: "POST",
      data: {
        pc_h: pc_h,
        pc_w: pc_w,
        pc_d: pc_d,
        pc_id, pc_id,
        paint_cost_model: paint_cost_model
      },
      success: function (data) {
        if (data) {
          $("#paint_cost").val(data);
        }
      },
    });


    var ac_qty = $("#ac_qty").val();
    $.ajax({
      url: "cost/calculate/acrylic_sheet.php",
      method: "POST",
      data: {
        ac_qty: ac_qty,
        pc_id: pc_id
      },
      success: function (data) {
        if (data) {
          $("#acrylic_cost").val(data);
        }
      },
    });

    var gk_qty = $("#gk_qty").val();
    $.ajax({
      url: "cost/calculate/gas_kit.php",
      method: "POST",
      data: {
        gk_qty: gk_qty,
        pc_id: pc_id,
      },
      success: function (data) {
        if (data) {
          $("#gk_cost").val(data);
        }
      },
    });

    var ibolt_qty = $("#ibolt_qty").val();
    $.ajax({
      url: "cost/calculate/ibolt.php",
      method: "POST",
      data: {
        ibolt_qty: ibolt_qty,
        pc_id: pc_id
      },
      success: function (data) {
        if (data) {
          $("#ibolt_cost").val(data);
        }
      },
    });

    var cd_model = $("#cd_model").val();
    var cd_qty = $("#cd_qty").val();
    $.ajax({
      url: "cost/calculate/cable_duct.php",
      method: "POST",
      data: {
        cd_model: cd_model,
        cd_qty: cd_qty,
        pc_id: pc_id
      },
      success: function (data) {
        if (data) {
          $("#cd_cost").val(data);
        }
      },
    });

    var lock_model = $("#lock_model").val();
    var lock_qty = $("#lock_qty").val();
    $.ajax({
      url: "cost/calculate/lock.php",
      method: "POST",
      data: {
        lock_model: lock_model,
        lock_qty: lock_qty,
        pc_id: pc_id
      },
      success: function (data) {
        if (data) {
          $("#lock_cost").val(data);
        }
      },
    });


    var bb_dimension = $("#bb_dimension").val();
    var busbar_qty = $("#busbar_qty").val();
    var busbar_weight = $("#busbar_weight").val();
    var busbar_sleeve = $("#busbar_sleeve").val();

    var bb_dimension_two = $("#bb_dimension_two").val();
    var busbar_qty_two = $("#busbar_qty_two").val();
    var busbar_weight_two = $("#busbar_weight_two").val();
    var busbar_sleeve_two = $("#busbar_sleeve_two").val();

    var bb_dimension_three = $("#bb_dimension_three").val();
    var busbar_qty_three = $("#busbar_qty_three").val();
    var busbar_weight_three = $("#busbar_weight_three").val();
    var busbar_sleeve_three = $("#busbar_sleeve_three").val();

    var bb_dimension_four = $("#bb_dimension_four").val();
    var busbar_qty_four = $("#busbar_qty_four").val();
    var busbar_weight_four = $("#busbar_weight_four").val();
    var busbar_sleeve_four = $("#busbar_sleeve_four").val();

    var bb_dimension_five = $("#bb_dimension_five").val();
    var busbar_qty_five = $("#busbar_qty_five").val();
    var busbar_weight_five = $("#busbar_weight_five").val();
    var busbar_sleeve_five = $("#busbar_sleeve_five").val();

    var bb_dimension_six = $("#bb_dimension_six").val();
    var busbar_qty_six = $("#busbar_qty_six").val();
    var busbar_weight_six = $("#busbar_weight_six").val();
    var busbar_sleeve_six = $("#busbar_sleeve_six").val();

    var bb_dimension_seven = $("#bb_dimension_seven").val();
    var busbar_qty_seven = $("#busbar_qty_seven").val();
    var busbar_weight_seven = $("#busbar_weight_seven").val();
    var busbar_sleeve_seven = $("#busbar_sleeve_seven").val();

    var bb_dimension_eight = $("#bb_dimension_eight").val();
    var busbar_qty_eight = $("#busbar_qty_eight").val();
    var busbar_weight_eight = $("#busbar_weight_eight").val();
    var busbar_sleeve_eight = $("#busbar_sleeve_eight").val();

    var bb_dimension_nine = $("#bb_dimension_nine").val();
    var busbar_qty_nine = $("#busbar_qty_nine").val();
    var busbar_weight_nine = $("#busbar_weight_nine").val();
    var busbar_sleeve_nine = $("#busbar_sleeve_nine").val();

    var bb_dimension_ten = $("#bb_dimension_ten").val();
    var busbar_qty_ten = $("#busbar_qty_ten").val();
    var busbar_weight_ten = $("#busbar_weight_ten").val();
    var busbar_sleeve_ten = $("#busbar_sleeve_ten").val();

    var bb_dimension_eleven = $("#bb_dimension_eleven").val();
    var busbar_qty_eleven = $("#busbar_qty_eleven").val();
    var busbar_weight_eleven = $("#busbar_weight_eleven").val();
    var busbar_sleeve_eleven = $("#busbar_sleeve_eleven").val();

    var bb_dimension_twelve = $("#bb_dimension_twelve").val();
    var busbar_qty_twelve = $("#busbar_qty_twelve").val();
    var busbar_weight_twelve = $("#busbar_weight_twelve").val();
    var busbar_sleeve_twelve = $("#busbar_sleeve_twelve").val();

    var bb_dimension_thirteen = $("#bb_dimension_thirteen").val();
    var busbar_qty_thirteen = $("#busbar_qty_thirteen").val();
    var busbar_weight_thirteen = $("#busbar_weight_thirteen").val();
    var busbar_sleeve_thirteen = $("#busbar_sleeve_thirteen").val();

    var bb_dimension_fourteen = $("#bb_dimension_fourteen").val();
    var busbar_qty_fourteen = $("#busbar_qty_fourteen").val();
    var busbar_weight_fourteen = $("#busbar_weight_fourteen").val();
    var busbar_sleeve_fourteen = $("#busbar_sleeve_fourteen").val();

    var bb_dimension_fifteen = $("#bb_dimension_fifteen").val();
    var busbar_qty_fifteen = $("#busbar_qty_fifteen").val();
    var busbar_weight_fifteen = $("#busbar_weight_fifteen").val();
    var busbar_sleeve_fifteen = $("#busbar_sleeve_fifteen").val();

    $.ajax({
      url: "cost/calculate/bus_bar.php",
      method: "POST",
      data: {
        bb_dimension: bb_dimension,
        busbar_qty: busbar_qty,
        busbar_weight: busbar_weight,
        busbar_sleeve: busbar_sleeve,
        bb_dimension_two: bb_dimension_two,
        busbar_qty_two: busbar_qty_two,
        busbar_weight_two: busbar_weight_two,
        busbar_sleeve_two: busbar_sleeve_two,
        bb_dimension_three: bb_dimension_three,
        busbar_qty_three: busbar_qty_three,
        busbar_weight_three: busbar_weight_three,
        busbar_sleeve_three: busbar_sleeve_three,
        bb_dimension_four: bb_dimension_four,
        busbar_qty_four: busbar_qty_four,
        busbar_weight_four: busbar_weight_four,
        busbar_sleeve_four: busbar_sleeve_four,
        bb_dimension_five: bb_dimension_five,
        busbar_qty_five: busbar_qty_five,
        busbar_weight_five: busbar_weight_five,
        busbar_sleeve_five: busbar_sleeve_five,
        bb_dimension_six: bb_dimension_six,
        busbar_qty_six: busbar_qty_six,
        busbar_weight_six: busbar_weight_six,
        busbar_sleeve_six: busbar_sleeve_six,
        bb_dimension_seven:  bb_dimension_seven,
        busbar_qty_seven: busbar_qty_seven,
        busbar_weight_seven: busbar_weight_seven,
        busbar_sleeve_seven: busbar_sleeve_seven,
        bb_dimension_eight: bb_dimension_eight,
        busbar_qty_eight: busbar_qty_eight,
        busbar_weight_eight: busbar_weight_eight,
        busbar_sleeve_eight: busbar_sleeve_eight,
        bb_dimension_nine: bb_dimension_nine,
        busbar_qty_nine: busbar_qty_nine,
        busbar_weight_nine: busbar_weight_nine,
        busbar_sleeve_nine: busbar_sleeve_nine,
        bb_dimension_ten: bb_dimension_ten,
        busbar_qty_ten: busbar_qty_ten,
        busbar_weight_ten: busbar_weight_ten,
        busbar_sleeve_ten: busbar_sleeve_ten,
        bb_dimension_eleven: bb_dimension_eleven,
        busbar_qty_eleven: busbar_qty_eleven,
        busbar_weight_eleven: busbar_weight_eleven,
        busbar_sleeve_eleven: busbar_sleeve_eleven,
        bb_dimension_twelve: bb_dimension_twelve,
        busbar_qty_twelve:  busbar_qty_twelve,
        busbar_weight_twelve: busbar_weight_twelve,
        busbar_sleeve_twelve: busbar_sleeve_twelve,
        bb_dimension_thirteen: bb_dimension_thirteen,
        busbar_qty_thirteen: busbar_qty_thirteen,
        busbar_weight_thirteen: busbar_weight_thirteen,
        busbar_sleeve_thirteen: busbar_sleeve_thirteen,
        bb_dimension_fourteen: bb_dimension_fourteen,
        busbar_qty_fourteen: busbar_qty_fourteen,
        busbar_weight_fourteen: busbar_weight_fourteen,
        busbar_sleeve_fourteen: busbar_sleeve_fourteen,
        bb_dimension_fifteen: bb_dimension_fifteen,
        busbar_qty_fifteen: busbar_qty_fifteen,
        busbar_weight_fifteen: busbar_weight_fifteen,
        busbar_sleeve_fifteen: busbar_sleeve_fifteen,
        pc_id: pc_id
      },
      success: function (data) {
        if (data) {
          var respData = JSON.parse(data);
          $("#bbr_total_weight").val(respData.weight_kg);
          $("#busbar_total_cost").val(respData.total_cost);
          $("#busbar_total_sleeve").val(respData.sleeve_cost);
        }
      },
    });

    var door1_h = $("#door1_h").val();
    var pc_type = $("#pc_type").val();
    var door1_w = $("#door1_w").val();
    var door1_d = $("#door1_d").val();
    var door1_cp = $("#door1_cp").val();
    var door2_h = $("#door2_h").val();
    var door2_w = $("#door2_w").val();
    var door2_d = $("#door2_d").val();
    var door2_cp = $("#door2_cp").val();
    var door3_h = $("#door3_h").val();
    var door3_w = $("#door3_w").val();
    var door3_d = $("#door3_d").val();
    var door3_cp = $("#door3_cp").val();
    var door4_h = $("#door4_h").val();
    var door4_w = $("#door4_w").val();
    var door4_d = $("#door4_d").val();
    var door4_cp = $("#door4_cp").val();
    var door5_h = $("#door5_h").val();
    var door5_w = $("#door5_w").val();
    var door5_d = $("#door5_d").val();
    var door5_cp = $("#door5_cp").val();
    var door6_h = $("#door6_h").val();
    var door6_w = $("#door6_w").val();
    var door6_d = $("#door6_d").val();
    var door6_cp = $("#door6_cp").val();
    var door7_h = $("#door7_h").val();
    var door7_w = $("#door7_w").val();
    var door7_d = $("#door7_d").val();
    var door7_cp = $("#door7_cp").val();
    var door8_h = $("#door8_h").val();
    var door8_w = $("#door8_w").val();
    var door8_d = $("#door8_d").val();
    var door8_cp = $("#door8_cp").val();
    $.ajax({
      url: "cost/calculate/doors.php",
      method: "POST",
      data: {
        pc_type: pc_type,
        door1_h: door1_h,
        door1_w: door1_w,
        door1_d: door1_d,
        door1_cp: door1_cp,
        door2_h: door2_h,
        door2_w: door2_w,
        door2_d: door2_d,
        door2_cp: door2_cp,
        door3_h: door3_h,
        door3_w: door3_w,
        door3_d: door3_d,
        door3_cp: door3_cp,
        door4_h: door4_h,
        door4_w: door4_w,
        door4_d: door4_d,
        door4_cp: door4_cp,
        door5_h: door5_h,
        door5_w: door5_w,
        door5_d: door5_d,
        door5_cp: door5_cp,
        door6_h: door6_h,
        door6_w: door6_w,
        door6_d: door6_d,
        door6_cp: door6_cp,
        door7_h: door7_h,
        door7_w: door7_w,
        door7_d: door7_d,
        door7_cp: door7_cp,
        door8_h: door8_h,
        door8_w: door8_w,
        door8_d: door8_d,
        door8_cp: door8_cp,
        pc_id: pc_id
      },
      success: function (data) {
        if (data) {
          $("#sheet_use").val(data);
        }
      },
    });

    var sheet_use = $("#sheet_use").val();
    var sheet_selection = $("#sheet_selection").val();
    var wiring_cost = $("#wiring_cost").val();
    var labour = $("#labour").val();
    var misc_exp = $("#misc_exp").val();
    var rent = $("#rent").val();
    var Increase_percent_14 = $("#Increase_percent_14").val();
    var Increase_percent_16 = $("#Increase_percent_16").val();
    var Increase_percent_18 = $("#Increase_percent_18").val();

    $.ajax({
      url: "cost/calculate/total_swg_cost.php",
      method: "POST",
      data: {
        wiring_cost: wiring_cost,
        labour: labour,
        misc_exp: misc_exp,
        rent: rent,
        Increase_percent_14: Increase_percent_14,
        Increase_percent_16: Increase_percent_16,
        Increase_percent_18: Increase_percent_18,
        sheet_use: sheet_use,
        sheet_selection: sheet_selection,
        pc_id: pc_id
      },
      success: function (data) {
        if (data) {
          var respData = JSON.parse(data);
          $("#sheet_weight_14").val(respData.swg14_sw);
          $("#sheet_weight_16").val(respData.swg16_sw);
          $("#sheet_weight_18").val(respData.swg18_sw);
          $("#sheet_cost_14").val(respData.swg14_cost);
          $("#sheet_cost_16").val(respData.swg16_cost);
          $("#sheet_cost_18").val(respData.swg18_cost);

          $("#swg_14_total").val(respData.swg14_total_cost);
          $("#swg_14_final_total").val(respData.swg14_final_cost);
          $("#swg_16_total").val(respData.swg16_total_cost);
          $("#swg_16_final_total").val(respData.swg16_final_cost);
          $("#swg_18_total").val(respData.swg18_total_cost);
          $("#swg_18_final_total").val(respData.swg18_final_cost);
        }
      },
    });

  } else {
    Swal.fire("Please Enter Values First");
  }
});


$(document).on("click", "#save_exit", function () {
  var sheet_sheet_cd = $("#sheet_sheet_cd").val();
  if(sheet_sheet_cd != ""){
  if (confirm("Once you saved you won't be able to make changes any more?") == true) {
    var pc_h = $("#swg_14_final_total").val();
    var pc_id = $(".pc_id").val();
    if (pc_h != "") {
      $.ajax({
        url: "cost/save_exit.php",
        method: "POST",
        data: {
          pc_id: pc_id
        },
        success: function (data) {
          if (data) {
            window.close();
          }
        },
      });
    }
    else {
      Swal.fire("It seems you have't calculated or stored all of values");
    }
  }
  else{}
}
else{
  Swal.fire("Please select which sheet value to display in cash demand page");
}
});