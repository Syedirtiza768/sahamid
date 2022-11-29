

function sum() {
  var pc_id = $("#pc_id").val();
  var paintcost_budget = $("#paintcost_budget").val();
  var rent_budget = $("#rent_budget").val();
  var misc_exp_budget = $("#misc_exp_budget").val();
  var labour_budget = $("#labour_budget").val();
  var bbr_sleeve_budget = $("#bbr_sleeve_budget").val();
  var ms_sheet_budget = $("#ms_sheet_budget").val();
  var cable_budget = $("#cable_budget").val();
  var hinges_budget = $("#hinges_budget").val();
  var lock_budget = $("#lock_budget").val();
  var acrylic_budget = $("#acrylic_budget").val();
  var gaskit_budget = $("#gaskit_budget").val();
  var cd_budget = $("#cd_budget").val();
  var ibolt_budget = $("#ibolt_budget").val();

  $.ajax({
    url: "cash_demand_cost/sumfunction.php",
    method: "POST",
    data: {
      pc_id: pc_id,
      paintcost_budget: paintcost_budget,
      rent_budget: rent_budget,
      misc_exp_budget: misc_exp_budget,
      labour_budget: labour_budget,
      bbr_sleeve_budget: bbr_sleeve_budget,
      ms_sheet_budget: ms_sheet_budget,
      cable_budget: cable_budget,
      hinges_budget: hinges_budget,
      lock_budget: lock_budget,
      acrylic_budget: acrylic_budget,
      gaskit_budget: gaskit_budget,
      cd_budget: cd_budget,
      ibolt_budget: ibolt_budget,
    },
    success: function (data) {
      $("#cashdemand_total").val(data);
    },
  });
}


document.getElementById('paintcost_qty').onchange = function () {
  var paint_cost_qty = $("#paintcost_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/paintcost_budget.php",
    method: "POST",
    data: {
      paint_cost_qty: paint_cost_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#paintcost_budget").val(data);
      sum();
      profit_paintcost(data);
    },
  });
};

function profit_paintcost(budget) {
  var paintcost_budget = budget;
  var paintcost_actual = $("#paintcost_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/paintcost_profit.php",
    method: "POST",
    data: {
      paintcost_actual: paintcost_actual,
      paintcost_budget: paintcost_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#paintcost_profit").val(data);
      sum();
      sum();
    },
  });
}
document.getElementById('paintcost_actual').onchange = function () {
  var paintcost_budget = $("#paintcost_budget").val();
  profit_paintcost(paintcost_budget);
};



document.getElementById('rent_qty').onchange = function () {
  var rent_qty = $("#rent_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/rent_budget.php",
    method: "POST",
    data: {
      rent_qty: rent_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#rent_budget").val(data);
      sum();
      sum();
      profit_rent(data);
    },
  });
};

function profit_rent(budget) {
  var rent_budget = budget;
  var rent_actual = $("#rent_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/rent_profit.php",
    method: "POST",
    data: {
      rent_actual: rent_actual,
      rent_budget: rent_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#rent_profit").val(data);
      sum();
    },
  });
}
document.getElementById('rent_actual').onchange = function () {
  var rent_budget = $("#rent_budget").val();
  profit_rent(rent_budget);
};


//misc_exp
document.getElementById('misc_exp_qty').onchange = function () {
  var misc_exp_qty = $("#misc_exp_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/misc_exp_budget.php",
    method: "POST",
    data: {
      misc_exp_qty: misc_exp_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#misc_exp_budget").val(data);
      sum();
      profit_misc(data);
    },
  });
};

function profit_misc(budget) {
  var misc_exp_budget = budget;
  var misc_exp_actual = $("#misc_exp_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/misc_exp_profit.php",
    method: "POST",
    data: {
      misc_exp_actual: misc_exp_actual,
      misc_exp_budget: misc_exp_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#misc_exp_profit").val(data);
      sum();
    },
  });
}
document.getElementById('misc_exp_actual').onchange = function () {
  var misc_exp_budget = $("#misc_exp_budget").val();
  profit_misc(misc_exp_budget);
};


//labour
document.getElementById('labour_qty').onchange = function () {
  var labour_qty = $("#labour_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/labour_budget.php",
    method: "POST",
    data: {
      labour_qty: labour_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#labour_budget").val(data);
      sum();
      profit_labour(data);
    },
  });
};

function profit_labour(budget) {
  var labour_budget = budget;
  var labour_actual = $("#labour_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/labour_profit.php",
    method: "POST",
    data: {
      labour_actual: labour_actual,
      labour_budget: labour_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#labour_profit").val(data);
      sum();
    },
  });
}
document.getElementById('labour_actual').onchange = function () {
  var labour_budget = $("#labour_budget").val();
  profit_labour(labour_budget);
};


//bbr_sleeve
document.getElementById('bbr_sleeve_qty').onchange = function () {
  var bbr_sleeve_qty = $("#bbr_sleeve_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/bbr_sleeve_budget.php",
    method: "POST",
    data: {
      bbr_sleeve_qty: bbr_sleeve_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#bbr_sleeve_budget").val(data);
      sum();
      profit_bbr(data);
    },
  });
};

function profit_bbr(budget) {
  var bbr_sleeve_budget = budget;
  var bbr_sleeve_actual = $("#bbr_sleeve_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/bbr_sleeve_profit.php",
    method: "POST",
    data: {
      bbr_sleeve_actual: bbr_sleeve_actual,
      bbr_sleeve_budget: bbr_sleeve_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#bbr_sleeve_profit").val(data);
      sum();
    },
  });
}
document.getElementById('bbr_sleeve_actual').onchange = function () {
  var bbr_sleeve_budget = $("#bbr_sleeve_budget").val();
  profit_bbr(bbr_sleeve_budget);
};


//ms_sheet
document.getElementById('ms_sheet_qty').onchange = function () {
  var ms_sheet_qty = $("#ms_sheet_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/ms_sheet_budget.php",
    method: "POST",
    data: {
      ms_sheet_qty: ms_sheet_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#ms_sheet_budget").val(data);
      sum();
      profit_ms(data);
    },
  });
};

function profit_ms(budget) {
  var ms_sheet_budget = budget;
  var ms_sheet_actual = $("#ms_sheet_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/ms_sheet_profit.php",
    method: "POST",
    data: {
      ms_sheet_actual: ms_sheet_actual,
      ms_sheet_budget: ms_sheet_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#ms_sheet_profit").val(data);
      sum();
    },
  });
}
document.getElementById('ms_sheet_actual').onchange = function () {
  var ms_sheet_budget = $("#ms_sheet_budget").val();
  profit_ms(ms_sheet_budget);
};


//cable
document.getElementById('cable_qty').onchange = function () {
  var cable_qty = $("#cable_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/cable_budget.php",
    method: "POST",
    data: {
      cable_qty: cable_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#cable_budget").val(data);
      sum();
      profit_cable(data);
    },
  });
};

function profit_cable(budget) {
  var cable_budget = budget;
  var cable_actual = $("#cable_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/cable_profit.php",
    method: "POST",
    data: {
      cable_actual: cable_actual,
      cable_budget: cable_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#cable_profit").val(data);
      sum();
    },
  });
}
document.getElementById('cable_actual').onchange = function () {
  var cable_budget = $("#cable_budget").val();
  profit_cable(cable_budget);
};


//hinges
document.getElementById('hinges_qty').onchange = function () {
  var hinges_qty = $("#hinges_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/hinges_budget.php",
    method: "POST",
    data: {
      hinges_qty: hinges_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#hinges_budget").val(data);
      sum();
      profit_hinges(data);
    },
  });
};

function profit_hinges(budget) {
  var hinges_budget = budget;
  var hinges_actual = $("#hinges_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/hinges_profit.php",
    method: "POST",
    data: {
      hinges_actual: hinges_actual,
      hinges_budget: hinges_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#hinges_profit").val(data);
      sum();
    },
  });
}
document.getElementById('hinges_actual').onchange = function () {
  var hinges_budget = $("#hinges_budget").val();
  profit_hinges(hinges_budget);
};


//lock
document.getElementById('lock_qty').onchange = function () {
  var lock_qty = $("#lock_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/lock_budget.php",
    method: "POST",
    data: {
      lock_qty: lock_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#lock_budget").val(data);
      sum();
      profit_lock(data);
    },
  });
};

function profit_lock(budget) {
  var lock_budget = budget;
  var lock_actual = $("#lock_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/lock_profit.php",
    method: "POST",
    data: {
      lock_actual: lock_actual,
      lock_budget: lock_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#lock_profit").val(data);
      sum();
    },
  });
}
document.getElementById('lock_actual').onchange = function () {
  var lock_budget = $("#lock_budget").val();
  profit_lock(lock_budget);
};


//acrylic
document.getElementById('acrylic_qty').onchange = function () {
  var acrylic_qty = $("#acrylic_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/acrylic_budget.php",
    method: "POST",
    data: {
      acrylic_qty: acrylic_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#acrylic_budget").val(data);
      sum();
      profit_acrylic(data);
    },
  });
};

function profit_acrylic(budget) {
  var acrylic_budget = budget;
  var acrylic_actual = $("#acrylic_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/acrylic_profit.php",
    method: "POST",
    data: {
      acrylic_actual: acrylic_actual,
      acrylic_budget: acrylic_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#acrylic_profit").val(data);
      sum();
    },
  });
}
document.getElementById('acrylic_actual').onchange = function () {
  var acrylic_budget = $("#acrylic_budget").val();
  profit_acrylic(acrylic_budget);
};


//gaskit
document.getElementById('gaskit_qty').onchange = function () {
  var gaskit_qty = $("#gaskit_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/gaskit_budget.php",
    method: "POST",
    data: {
      gaskit_qty: gaskit_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#gaskit_budget").val(data);
      sum();
      profit_gaskit(data);
    },
  });
};

function profit_gaskit(budget) {
  var gaskit_budget = budget;
  var gaskit_actual = $("#gaskit_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/gaskit_profit.php",
    method: "POST",
    data: {
      gaskit_actual: gaskit_actual,
      gaskit_budget: gaskit_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#gaskit_profit").val(data);
      sum();
    },
  });
}
document.getElementById('gaskit_actual').onchange = function () {
  var gaskit_budget = $("#gaskit_budget").val();
  profit_gaskit(gaskit_budget);
};


//cd
document.getElementById('cd_qty').onchange = function () {
  var cd_qty = $("#cd_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/cd_budget.php",
    method: "POST",
    data: {
      cd_qty: cd_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#cd_budget").val(data);
      sum();
      profit_cd(data);
    },
  });
};

function profit_cd(budget) {
  var cd_budget = budget;
  var cd_actual = $("#cd_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/cd_profit.php",
    method: "POST",
    data: {
      cd_actual: cd_actual,
      cd_budget: cd_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#cd_profit").val(data);
      sum();
    },
  });
}
document.getElementById('cd_actual').onchange = function () {
  var cd_budget = $("#cd_budget").val();
  profit_cd(cd_budget);
};



//ibolt
document.getElementById('ibolt_qty').onchange = function () {
  var ibolt_qty = $("#ibolt_qty").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/ibolt_budget.php",
    method: "POST",
    data: {
      ibolt_qty: ibolt_qty,
      pc_id: pc_id

    },
    success: function (data) {
      $("#ibolt_budget").val(data);
      sum();
      profit_ibolt(data);
    },
  });
};

function profit_ibolt(budget) {
  var ibolt_budget = budget;
  var ibolt_actual = $("#ibolt_actual").val();
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/ibolt_profit.php",
    method: "POST",
    data: {
      ibolt_actual: ibolt_actual,
      ibolt_budget: ibolt_budget,
      pc_id: pc_id

    },
    success: function (data) {
      $("#ibolt_profit").val(data);
      sum();
    },
  });
}
document.getElementById('ibolt_actual').onchange = function () {
  var ibolt_budget = $("#ibolt_budget").val();
  profit_ibolt(ibolt_budget);
};


$(document).ready(function () {
  var pc_id = $("#pc_id").val();
  $.ajax({
    url: "cash_demand_cost/sum.php",
    method: "POST",
    data: {
      pc_id: pc_id
    },
    success: function (data) {
      $("#cashdemand_total").val(data);
      sum();
    },
  });
});