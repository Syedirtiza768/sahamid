var dirtyEditors = [];
var brandsArray = {};
var table = null;
var selectedLine = 0;
var selectedOption = 0;
var stack_topleft = { "dir1": "down", "dir2": "right", "push": "top" };
var creditLimit = 0;
var currentCredit = 0;
NProgress.start();
//Document Ready
$(document).ready(function () {
	$('[data-toggle="popover"]').popover();
	$('#chooseLine').hide();
	$('#chooseOption').hide();
	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	$('#rendering').addClass("rendering");

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/retrieveQuotation.php",
		data: { orderno: order },
		dataType: "json",
		success: function (response) {
			var status = response.status;


			if (status == "success") {
				var data = response.data;
				window.totalOutstanding = parseInt(response.credit);
				window.currentCredit = window.totalOutstanding ? window.totalOutstanding : 0;
				window.creditLimit = parseInt(data.creditlimit);
				var status = response.status;
				var lines = data.lines;

				$('#clientnamebasic').html(data.name);
				$('#clientlocationbasic').html(data.locationname);
				$('#salesman').html(data.salesmann);
				if (data.existing == 1)
					$('#eorderno').html(data.eorderno);
				else
					$('#eorderno').html("New Quotation");
				window.debtorno = data.debtorno;
				var quotTotal = 0;

				$('.lineoptiontotal').each(function () {
					quotTotal += Number($(this).html());
				});

				let overLimit = "";
				if ((window.currentCredit + quotTotal) > window.creditLimit) {
					$('#totalquotationvalue').css("color", "red");
					overLimit = ` ( ${window.creditLimit - (window.currentCredit + quotTotal)} Over Credit Limit)`;
				} else {
					$('#totalquotationvalue').css("color", "#424242");
					overLimit = `${window.creditLimit - (window.currentCredit + quotTotal)}`;
				}

				$("#order-details").find(".deliverto").val(data.deliverto);
				$("#order-details").find(".deliverydate").val(data.deliverydate);
				$("#order-details").find(".quotedate").val(data.quotedate);
				$("#order-details").find(".confirmeddate").val(data.confirmeddate);
				$("#order-details").find(".deladd1").val(data.deladd1);
				$("#order-details").find(".deladd2").val(data.deladd2);
				$("#order-details").find(".deladd3").val(data.deladd3);
				$("#order-details").find(".deladd4").val(data.deladd4);
				$("#order-details").find(".deladd5").val(data.deladd5);
				$("#order-details").find(".deladd6").val(data.deladd6);
				$("#order-details").find(".contactperson").val(data.contactperson);
				$("#order-details").find(".contactphone").val(data.contactphone);
				$("#order-details").find(".contactemail").val(data.contactemail);
				$("#order-details").find(".customerref").val(data.customerref);
				$("#order-details").find(".advance").val(data.advance);
				$("#order-details").find(".delivery").val(data.delivery);
				$("#order-details").find(".commisioning").val(data.commisioning);
				$("#order-details").find(".after").val(data.after);
				$("#order-details").find(".afterdays").val(data.afterdays);
				$("#order-details").find(".GSTadd").val(data.GSTadd);
				$("#order-details").find(".services").prop('checked', (data.services == 1));
				//let printexchangeflag=$("#order-details").find(".printexchange").attr("value");
				console.log(data.printexchange);
				$("#order-details").find(".printexchange1").prop('checked', (data.printexchange == 0));
				$("#order-details").find(".printexchange2").prop('checked', (data.printexchange == 1));
				$("#order-details").find(".printexchange3").prop('checked', (data.printexchange == 2));

				$("#order-details").find(".fromstkloc").val(data.fromstkloc);
				$("#order-details").find(".gst").val(data.gst);
				$("#order-details").find(".WHT").val(data.WHT);
				$("#order-details").find(".freightclause").val(data.freightclause);
				$("#order-details").find(".comments").val(data.comments);
				$("#order-details").find(".umqd").prop('checked', (data.umqd == 1));
				$("#order-details").find(".validity").val(data.validity);
				$("#order-details").find(".rate_clause").val(data.rate_clause);
				$("#order-details").find(".rate_validity").val(data.rate_validity);
				$("#quickQuotation").val(data.quickQuotation);
				formID = response.formid;
				window.formID = response.formid;
				window.flag = response.flag;
				let statementLink = `
		<form id="printStatementForm" action="/sahamid/reports/balance/custstatement/../../../customerstatement.php" method="post" target="_blank">
			<input type="hidden" name="FormID" value="${window.formID}">
			<input type="hidden" name="cust" value="${window.debtorno}">
			<input type="submit" value="Customer Statement" class="btn-info" style="padding: 8px; border:1px #424242 solid;">
		</form>
	`
				let html = "<span style='font-size: larger;'><b>Kindly share the ledger with the customer. Your cooperation is highly valuable for the company</b></span>";
				html += `<center><table border="2" style="font-size: large;color:red;">
				
				<tr><td> &nbsp;Total Outstanding &nbsp;</td><td> &nbsp;Credit Remaining &nbsp;</td></tr>` +
					`<tr><td>${(Math.round(window.totalOutstanding).toLocaleString())}</td><td>${(overLimit)}</td></tr></table></center><br/>`;
				html += `<center>${(statementLink)}</center>`;
				//	$('#totalquotationvalue').html(`<table>Total Outstanding (${(Math.round(window.totalOutstanding).toLocaleString())}) Document Total: `+Math.round(quotTotal).toLocaleString()+overLimit.toLocaleString());
				if (window.flag != "on") {
					$('#totalquotationvalue').html(html);
				}

				if (window.flag != "on") {
					swal({
						title: "Alert!!!",
						text: html,
						type: 'warning',
						confirmButtonColor: "#cc3f44",
						confirmButtonText: 'Print Statement',
						closeOnConfirm: true,
						html: true
					}, function () {
						confirmed = true;
						$("#printStatementForm").submit();
					});
				}



				$count = [];
				$i = 0;
				var line_count = 0;
				for (var index in lines) {

					NProgress.inc();

					if (!lines.hasOwnProperty(index)) {
						continue;
					}

					var lineid = lines[index].lineindex;
					var creq = lines[index].clientrequirements;
					var arr = [lineid, creq]
					$count[$i] = arr;
					var options = lines[index].options;
					var optionum = [];
					var i = 0;
					line_count = line_count + 1;
					for (var optionIndex in options) {
						var lineno = options[optionIndex].lineno;
						var optionno = options[optionIndex].optionindex;
						var opttext = options[optionIndex].optiontext;
						var stkstatus = options[optionIndex].stockstatus;
						var quanty = options[optionIndex].quantity;
						var uom = options[optionIndex].uom;
						var price = options[optionIndex].price;
						optionum[i] = [lineno,optionno,quanty];
						i++;
					}
					addlineCallback(lineid, creq, optionum);
					for (var optionIndex in options) {

						NProgress.inc();

						if (!options.hasOwnProperty(optionIndex)) {
							continue;
						}

						var lineno = options[optionIndex].lineno;
						var optionno = options[optionIndex].optionindex;
						var opttext = options[optionIndex].optiontext;
						var stkstatus = options[optionIndex].stockstatus;
						var quanty = options[optionIndex].quantity;
						var uom = options[optionIndex].uom;
						var price = options[optionIndex].price;

						var arr1 = [...arr, lineid, optionno, opttext, stkstatus, quanty, uom, price]
						$count[$i] = arr1;
						addoptionCallback(lineno, optionno, opttext, stkstatus, quanty, uom, price);
						$(".option_quantity").val(quanty);
						var items = options[optionIndex].items;
						var pricetot = 0;

						for (var itemIndex in items) {

							NProgress.inc();

							if (!items.hasOwnProperty(itemIndex)) {
								continue;
							}

							var indx = items[itemIndex].salesorderdetailsindex;
							var ln = items[itemIndex].orderlineno;
							var on = items[itemIndex].lineoptionno;
							var stkc = items[itemIndex].stkcode;
							var bname = items[itemIndex].manu_name;
							var des = items[itemIndex].description;
							var qty = items[itemIndex].quantity;
							var sprice = items[itemIndex].standardcost;
							var upr = items[itemIndex].unitprice;
							var urate = items[itemIndex].unitrate;
							if (upr == sprice)
								var disupdated = "";
							else
								var disupdated = "(updated)";

							console.log(urate);
							var qohal = items[itemIndex].qohatloc;
							if (urate > 0)
								var dis = (1 - urate / sprice);
							else
								var dis = items[itemIndex].discountpercent;

							var ud = items[itemIndex].lastcostupdate;
							var ub = items[itemIndex].lastupdatedby;
							var modal = items[itemIndex].mnfcode;
							var part = items[itemIndex].mnfpno;

							var update = ub + "(" + ud + ")";
							var uprice = 0;



							dis = dis * 100;

							if (dis != 0) {
								uprice = Math.round((sprice * 100) - (((sprice / 100) * dis) * 100)) / 100;
								//uprice = Math.round((uprice*100)-(((uprice/100)*dis)*100))/100;
							}
							else
								uprice = sprice;

							var tot = qty * uprice;

							pricetot += tot;

							var arr2 = [...arr1, indx, ln, on, stkc, bname, 0, qty, sprice, uprice, tot, qohal, dis, update, modal, part, disupdated];
							$count[$i] = arr2;
							additemCallback(indx, ln, on, stkc, bname, des, qty, sprice, uprice, tot, qohal, dis, update, modal, part, disupdated);

						}

						pricetot = Math.round(pricetot * 100) / 100;
						subtotal = Math.round(pricetot * quanty * 100) / 100;

						$("#optiondesc" + lineno + "-" + optionno + "").find(".pricecont").html(pricetot);
						$("#optiondesc" + lineno + "-" + optionno + "").find(".tpricecont").html(subtotal);
						$("#lt" + lineno + "ot" + optionno + "").html(subtotal);

						updateQuotationValue();

					}
					$i++
				}
				// alert($count.length);
				divVar($count);
				NProgress.set(0.7);

				$('#rendering').removeClass("rendering");
				$('#rendering').css("display", "none");
				NProgress.done();

			} else {

				NProgress.set(0.5);

				$('#rendering').removeClass("rendering");
				$('#rendering').html("<h1>" + response.message + "</h1>");

				NProgress.done();

			}


		},
		error: function () {
			swal("Error", "Some error occured when fetching the quotation.", "error");
			$('#rendering').removeClass("rendering");
			NProgress.done();
		}

	});

	setInterval(function () {
		checkDirtyAndUpdate();
	}, 15000);

});



//Description Update Save
function checkDirtyAndUpdate() {
	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	for (index in dirtyEditors) {

		if (dirtyEditors[index].length > 0) {

			var dEditor = dirtyEditors[index].pop();
			dirtyEditors[index].push(dEditor);

			var text = dEditor.editor.content.get();

			var msg = dEditor.msg;
			var editor = dEditor.editor;
			var line = dEditor.line;

			var option = dEditor.option;
			var type = "";

			if (option == 0)
				type = "cr";
			else
				type = "desc";

			if (dEditor.editor.content.isDirty()) {

				dEditor.editor.content.setDirty(false);

				$.ajax({
					type: 'POST',
					url: rootpath + "/quotation/api/saveDescription.php",
					data: { orderno: order, lineno: line, description: text, type: type, index: index, option: option },
					dataType: "json",
					success: function (response) {
						var status = response.status;

						if (status == "success") {

							var data = response.data;
							saveDescriptionCallback(data.index);

						}

					},
					error: function () {
						alert("Save Error");
					}
				});

			}
		}

	}

}

function saveDescriptionCallback(index) {

	var dedit = dirtyEditors[index].shift();

	var msg = dedit.msg;
	var editor = dedit.editor;

	msg.hide();
	editor.message('info', 3000, "Saved!");
}

//--------------


$('#closesearchoverlay').click(function () {
	$('#searchoverlay').css('display', 'none');
	table.clear().draw();
	$("#cicis").html("");
	$("#scat").val("All");
	$("#scode").val("");
});

function addline() {
	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();
	NProgress.start();
	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/createNewLine.php",
		data: { orderno: order, salescaseref: salesref },
		dataType: "json",
		success: function (response) {
			var status = response.status;

			if (status == "success") {
				var data = response.data;
				var line = data.line_id;
				var req = "";
				optionum[0] = [0,0,0];
				addlineCallback(line, req,optionum);
			} else {

				swal("Error", "Save Error", "error");

			}
			NProgress.done();
		},
		error: function () {
			swal("Error", "Save Error", "error");
			NProgress.done();
		}
	});
}

function addlineNewCallback(line, requirements) {
	var vline = vlineno += 1;
	options[line] = 0;
	voptions[line] = 0;
	html += '<header class="panel-heading">';
	var html = '<section id="l' + line + '" class="panel panel-featured">';
	html += '<div class="panel-actions">';
	html += '<a onclick="linetoggle(' + line + ')" class="panel-action panel-action-toggle" ></a>';
	html += '<a onclick="removeline(' + line + ',' + vline + ')" class="panel-action panel-action-dismiss" data-panel-dismiss=""></a>';
	html += '</div>';
	html += '<h2 class="panel-title lineheading">Line ' + vline + '</h2>';
	html += '</header>';
	html += '<div id="l' + line + 'body" class="panel-body">';
	html += '<div id="l' + line + 'oc">';
	html += '</div>';
	html += '<div id="l' + line + 'oa" class="pull-right">';
	html += '<button type="button" class="btn btn-primary" onclick="addoption(' + line + ')" name="button">Add New Option</button>';
	html += '</div>';
	html += '</div>';
	html += '</section>';

	var descHTML = '<section class="line" id="linedesc' + line + '" style="text-align:center; margin: 0 auto;">';
	descHTML += '<h3>Line No. ' + vline + ' Client Requirements </h3>';
	descHTML += '<textarea  style="width: 100%; height: 100%;" id = "l' + line + 'o0desc" cols="90" rows="10"></textarea>';
	descHTML += '<div id="optionline' + line + 'desccontainer" style="text-align:center; margin: 0 auto;"></div>';
	descHTML += '</section>';

	$('#linesdescriptioncontainer').append(descHTML);

	inittextboxio(line, 0, requirements);

	$('#linescontainer').append(html);
}


function addlineCallback(line, requirements, option) {
	var vline = vlineno += 1;
	var optionno = option;
	options[line] = 0;
	voptions[line] = 0;
	console.log(line.length)
	html += '<header class="panel-heading">';
	var html = '<section id="l' + line + '" class="panel panel-featured">';
	html += '<div class="panel-actions">';
	html += '<a onclick="linetoggle(' + line + ')" class="panel-action panel-action-toggle" ></a>';
	html += '<a onclick="removeline(' + line + ',' + vline + ')" class="panel-action panel-action-dismiss" data-panel-dismiss=""></a>';
	html += '</div>';
	html += '<h2 class="panel-title lineheading">Line ' + vline + '</h2>';
	html += '</header>';
	html += '<div id="l' + line + 'body" class="panel-body">';
	html += '<div id="l' + line + 'oc">';
	html += '</div>';
	html += '<div id="l' + line + 'oa" class="pull-right">';
	console.log(optionno);
	html += '<button type="button" class="btn btn-primary" onclick="optionsCount(' + optionno + ')" name="button">Add New Option</button>';
	html += '<div id="option' + optionno + 'list" class="pull-right"></div>';
	html += '</div>';
	html += '</div>';
	html += '</section>';

	var descHTML = '<section class="line" id="linedesc' + line + '" style="text-align:center; margin: 0 auto;">';
	descHTML += '<h3>Line No. ' + vline + ' Client Requirements </h3>';
	descHTML += '<textarea  style="width: 100%; height: 100%;" id = "l' + line + 'o0desc" cols="90" rows="10"></textarea>';
	descHTML += '<div id="optionline' + line + 'desccontainer" style="text-align:center; margin: 0 auto;"></div>';
	descHTML += '</section>';

	$('#linesdescriptioncontainer').append(descHTML);

	inittextboxio(line, 0, requirements);

	$('#linescontainer').append(html);
}

function addNewLine(line) {
	var lineNo = line;
	var manu_name = manu_name;
	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();
	var quantity = $(".option_quantity").val();
	NProgress.start();
	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/createNewLine.php",
		data: { orderno: order, salescaseref: salesref },
		dataType: "json",
		success: function (response) {
			var status = response.status;

			if (status == "success") {
				var data = response.data;
				var line = data.line_id;
				var req = "";
				addlineNewCallback(line, req);
				var order = $('#orderno').val();
				var rootpath = $('#rootpath').val();
				var salesref = $('#salesref').val();
				$.ajax({
					type: 'POST',
					url: rootpath + "/quotation/api/createNewOption.php",
					data: { orderno: order, salescaseref: salesref, lineno: line },
					dataType: "json",
					success: function (response) {
						var data = response.data;
						var line = data.line_id;
						var option = data.option_id;
						var req = "";
						$(".line").val(line);
						$(".option").val(option);
						addoptionCallback(line, option, req, "", quantity, "", 0);
						additempre(lineNo);

					}
				});


				// sessionStoragefun(line);
			} else {

				swal("Error", "Save Error", "error");

			}
			NProgress.done();
		},
		error: function () {
			swal("Error", "Save Error", "error");
			NProgress.done();
		}
	});


}


function additemcb(lineno) {
	var lineno = lineno;
	var line = $(".line").val();
	var option = $(".option").val();
	var optionno = $(".option_required").val();
	$.ajax({
		type: 'POST',
		url: "./quotation/api/retrieveQuotationitem.php",
		data: { lineno: lineno },
		success: function (response) {
			var res = $.parseJSON(response);
			var length = res.length;
			for (var i = 0; i < length; i++) {

				var indx = res[i]['salesorderdetailsindex'];
				var ln = res[i]['orderlineno'];
				ln = ln + 1;
				var on = res[i]['lineoptionno'];
				var stkc = res[i]['stkcode'];
				var bname = res[i]['manu_name'];
				var des = res[i]['description'];
				var qty = res[i]['quantity'];
				var sprice = res[i]['standardcost'];
				var upr = res[i]['unitprice'];
				var urate = res[i]['unitrate'];
				if (upr == sprice)
					var disupdated = "";
				else
					var disupdated = "(updated)";

				// var qohal = res[i]['qohatloc'];
				if (urate > 0)
					var dis = (1 - urate / sprice);
				else
					var dis = res[i]['discountpercent'];

				var ud = res[i]['lastcostupdate'];
				var ub = res[i]['lastupdatedby'];
				var modal = res[i]['mnfcode'];
				var part = res[i]['mnfpno'];

				var update = ub + "(" + ud + ")";
				var uprice = 0;



				dis = dis * 100;

				if (dis != 0) {
					uprice = Math.round((sprice * 100) - (((sprice / 100) * dis) * 100)) / 100;
					//uprice = Math.round((uprice*100)-(((uprice/100)*dis)*100))/100;
				}
				else
					uprice = sprice;
				var tot = qty * uprice;
				if(optionno == on){

					insertitemManually(stkc, bname, line, option, qty, uprice, dis, tot);
				}
				// pricetot += tot;

				// additemCallback(indx,ln,on,stkc,bname,des,qty,sprice,uprice,tot,"0",dis,update,modal,part,disupdated);
				
			}



		},
		error: function (xhr, status, error) {
			var err = eval("(" + xhr.responseText + ")");
			alert(err.Message);
		}

	});
}

function additempre(lineno){
	var lineno = lineno;
	var line= $(".line").val();
	var option =$(".option").val();
	$.ajax({
		type: 'POST',
		url: "./quotation/api/retrieveQuotationitem.php",
		data: {lineno: lineno},
		success: function(response) {
			var res = $.parseJSON(response);
			var length = res.length;
			for(var i=0; i<length; i++){
			
			var indx = res[i]['salesorderdetailsindex'];
			var ln = res[i]['orderlineno'];
			ln= ln+1;
			var on = res[i]['lineoptionno'];
			var stkc = res[i]['stkcode'];
			var bname = res[i]['manu_name'];
			var des = res[i]['description'];
			var qty = res[i]['quantity'];
			var sprice = res[i]['standardcost'];
			var upr = res[i]['unitprice'];
			var urate = res[i]['unitrate'];
			if(upr==sprice)
			var disupdated = "";
			else
				var disupdated = "(updated)";

			// var qohal = res[i]['qohatloc'];
			if (urate>0)
			var dis = (1-urate/sprice);
			else
			var dis = res[i]['discountpercent'];

			var ud = res[i]['lastcostupdate'];
			var ub = res[i]['lastupdatedby'];
			var modal = res[i]['mnfcode'];
			var part = res[i]['mnfpno'];

			var update = ub+"("+ud+")"; 
			var uprice = 0;
			
			

			dis = dis*100;

			if(dis != 0){
				uprice = Math.round((sprice*100)-(((sprice/100)*dis)*100))/100;
				//uprice = Math.round((uprice*100)-(((uprice/100)*dis)*100))/100;
			}
			else
				uprice = sprice;

			var tot = qty*uprice;

			// pricetot += tot;
			insertitemManually(stkc, bname, line, option,qty,uprice,dis,tot);
			
			// additemCallback(indx,ln,on,stkc,bname,des,qty,sprice,uprice,tot,"0",dis,update,modal,part,disupdated);
			}



		},
		error: function(xhr, status, error) {
			var err = eval("(" + xhr.responseText + ")");
			alert(err.Message);
		  }
		
		});
}

function newLine(id) {
	if (id == 'n') {
		addline();
	}
	else {
		var split = id.split(',');
		if (split[2] == null) { addline(); }
		else {
			addNewLine(split[0]);
			// 	}
		}
	}
}

function divVar(count) {
	var length = count.length;
	var html = '<h4><select name="line" id="line" onchange="newLine(this.value)">';
	html += '<option >Choose one</option>'
	var j = 1;
	for (i = 0; i < length; i++) {
		html += '<option value="' + count[i] + '">Line ' + j + '</option>';
		j++;
	}
	html += '<option value="n">Create New Line</option>';
	html += '</select></h4>';
	$('#chooseLine').append(html);
}

function optionsCount(...optionno) {
	length = optionno.length;
	console.log(optionno);
	line= optionno[0];
	
	var html = '<h4><select name="line" id="option" onchange="addNewOption(this.value, line)">';
	html += '<option>Choose one</option>'
	var j = 1;
	var i = 0;
	var z=0;
	
	if(optionno[2]== "0"){
		html += '<option value="n">Create New Option</option>';
	}
	else{
		var inc=1;
	for (i = 1; i < length-j; i+=2) {
		html += '<option value="'+optionno[i+inc]+',' + optionno[i+z] + '">Option ' + j + '</option>';
		inc = inc +1
		j++;
		z++;
	}
	html += '<option value="0,n">Create New Option</option>';
}
	$(document.getElementById("option" + optionno + "list")).append(html);
}

function addNewOption(option, line) {
	 var split = option.split(',');
	 option = split[1];
	$(".option_quantity").val(split[0]);
	if(option == "n"){
		addoption(line);
	}
	else{
		optionno= option;
		lineno = line;
		var quantity = $(".option_quantity").val();
		$(".option_required").val(optionno);
	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();
	
	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/createNewOption.php",
		data: { orderno: order, salescaseref: salesref, lineno: line },
		dataType: "json",
		success: function (response) {
			var data = response.data;
			var line = lineno
			var option = data.option_id;;
			$(".line").val(line);
			$(".option").val(option);
			var req = "";
			addoptionCallback(line, option, req, "", quantity , "", 0);
			additemcb(line);

		}
	});
}

}

function addoption(line) {

	NProgress.start();

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();
	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/createNewOption.php",
		data: { orderno: order, salescaseref: salesref, lineno: line },
		dataType: "json",
		success: function (response) {
			var status = response.status;

			if (status == "success") {
				var data = response.data;
				var line = data.line_id;
				var option = data.option_id;
				var req = "";
				addoptionCallback(line, option, req, "", 0, "", 0);
			} else {

				swal("Error", "Save Error", "error");

			}
			NProgress.done();
		},
		error: function () {
			NProgress.done();
			swal("Error", "Save Error", "error");
		}
	});

}

function addoptionCallback(line, option, description, stockstatus, quantity, uom, price) {
	var voption = voptions[line] += 1;
	items[line + "," + option] = 0;

	var html = '<section id="l' + line + 'o' + option + '" class="panel panel-featured panel-featured-primary">';
	html += '<header class="panel-heading">';
	html += '<div class="panel-actions">';
	html += '<strong style="font-size:1.6rem">Total: ( <span id="lt' + line + 'ot' + option + '" class="lineoptiontotal"></span> )</strong>';
	html += '<a onclick="optiontoggle(' + line + ',' + option + ')" class="panel-action panel-action-toggle" ></a>';
	html += '<a onclick="removeoption(' + line + ',' + option + ',' + voption + ')" class="panel-action panel-action-dismiss" data-panel-dismiss=""></a>';
	html += '</div>';
	html += '<h2 class="panel-title optionheading">Option ' + voption + '</h2>';
	html += '</header>';
	html += '<div id="l' + line + 'o' + option + 'body" class="panel-body">';
	html += '<div id="l' + line + 'o' + option + 'ic">';
	html += '</div>';
	html += '<div id="l' + line + 'o' + option + 'ia" class="pull-right" style="width:100%">';
	html += '<button type="button" class="btn btn-primary pull-right" onclick="addnewitem(' + line + ',' + option + ')" name="button">Add New Item</button>';
	html += '<button type="button" class="btn btn-primary pull-left" onclick="insertRegretItem(' + line + ',' + option + ')" name="button" style="margin-right:10px">Add Regret Item</button>';
	html += '<button type="button" class="btn btn-primary pull-left" onclick="insertWillQuoteLater(' + line + ',' + option + ')" name="button">Will Quote Later</button>';
	html += '</div>';
	html += '</div>';
	html += '</section>';

	var descHTML = '<section id="optiondesc' + line + '-' + option + '" style="text-align:center; margin: 0 auto;">';
	descHTML += '<h4 style="color:blue">Option No. ' + voption + ' SAH Description <button class="copyto btn btn-success">Copy To</button></h4>';
	descHTML += '<table border="1px" cellpadding="2" width="100%" style="background:#e2f5ff">';
	descHTML += '<thead style="text-align:center; background: #5a5a5a; color:white;">';
	descHTML += '<tr>';
	descHTML += '<th>';
	descHTML += 'Model #';
	descHTML += '</th>';
	descHTML += '<th>';
	descHTML += 'Part #';
	descHTML += '</th>';
	descHTML += '<th>';
	descHTML += 'Brand';
	descHTML += '</th>';
	descHTML += '<th>';
	descHTML += 'Description';
	descHTML += '</th>';
	descHTML += '<th>';
	descHTML += 'QOH';
	descHTML += '</th>';
	descHTML += '<th>';
	descHTML += 'Quantity';
	descHTML += '</th>';
	descHTML += '<th>';
	descHTML += 'Unit Price';
	descHTML += '</th>';
	descHTML += '<th>';
	descHTML += 'Discount';
	descHTML += '</th>';
	descHTML += '</tr>';
	descHTML += '</thead>';
	descHTML += '<tbody id="opdc' + line + '-' + option + '">';
	descHTML += '</tbody>';
	descHTML += '</table>';
	descHTML += '<textarea  style="width: 100%; height: 100%;" id = "l' + line + 'o' + option + 'desc" cols="90" rows="10"></textarea>';
	descHTML += '<div style="background:#ddd; padding:5px;">';
	descHTML += '<table class="det" width="100%" cellpadding="2">';
	descHTML += '<tbody>';
	descHTML += '<tr>';
	descHTML += '<td>';
	descHTML += 'Stock status';
	descHTML += '</td>';
	descHTML += '<td>';
	descHTML += '<input class="descinfo stockstatus" name="stockstatus" value="' + stockstatus + '">';
	descHTML += '</td>';
	descHTML += '<td>';
	descHTML += 'UOM';
	descHTML += '</td>';
	descHTML += '<td>';
	descHTML += '<input class="descinfo uomoption" name="uom" value="' + uom + '" data-line="' + line + '" data-option="' + option + '">';
	descHTML += '</td>';
	descHTML += '<td>';
	descHTML += '<b>Quantity: </b>';
	descHTML += '</td>';
	descHTML += '<td>';
	descHTML += '<input type="number" class="descinfo quantity" name="quantity" value="' + quantity + '">';
	descHTML += '</td>';
	descHTML += '<td>';
	descHTML += '<b>Price:</b>';
	descHTML += '</td>';
	descHTML += '<td class="pricecont">';
	descHTML += '';
	descHTML += '</td>';

	if ($("#quickQuotation").val() == "1") {

		descHTML += '<td>';
		descHTML += '<b>OpPrice:</b>';
		descHTML += '</td>';
		descHTML += '<td class="opPrice">';
		descHTML += '<input type="number" name="price" class="oppprice optionPrice" value="' + price + '" data-line="' + line + '" data-option="' + option + '" />';
		descHTML += '</td>';

	}

	descHTML += '<td>';
	descHTML += '<b>Sub Total:</b>';
	descHTML += '</td>';
	descHTML += '<td class="tpricecont">';
	descHTML += '';
	descHTML += '</td>';
	descHTML += '</tr>';
	descHTML += '</tbody>';
	descHTML += '</table>';
	descHTML += '</div>';
	descHTML += '<div id="optionline' + line + 'desccontainer" style="text-align:center; margin: 0 auto;"></div>';
	descHTML += '</section>';

	$('#optionline' + line + 'desccontainer').append(descHTML);

	inittextboxio(line, option, description);

	$('#l' + line + 'oc').append(html);

}

function addnewitem(line, option) {

	selectedLine = line;
	selectedOption = option;

	NProgress.start();

	var lt = $("#l" + line + "").find("header").find("h2").html();
	lineno = lt.split(" ")[1];

	$("#slnum").html(lineno);

	var ot = $("#l" + line + "o" + option + "").find("header").find("h2").html();
	optionno = ot.split(" ")[1];

	$("#sonum").html(optionno);

	$("#l" + line + "o" + option + "ic > section").each(function () {
		var itemcode = $(this).find("header").find("h2").html();
		var desc = $(this).find(".desc").html();
		var brand = $(this).find(".brandyo").html();

		var html = "<tr>";
		html += "<td>";
		html += itemcode;
		html += "</td>";
		html += "<td>";
		html += brand;
		html += "</td>";
		html += "<td>";
		html += desc;
		html += "</td>";
		html += "</tr>";

		$("#cicis").append(html);

	});

	$("#searchoverlay").css('display', 'block');

	NProgress.done();

}

function additemCallback(indx, line, option, stkc, bname, description, quantity, sprice, uprice, tot, qoh, dis, update, modal, part, disupdated) {

	var item = items[line + "," + option] += 1;

	var html = '<section id="l' + line + 'o' + option + 'i' + indx + '" class="panel panel-featured panel-featured-info">';

	html += '<header class="panel-heading">';
	html += '<div class="panel-actions">';
	html += '<form method="post" target="_blank" action="reorder/reorderRequest.php"><input type="hidden" name="FormID" value="' + $("#FormID").val() + '"/><input type="hidden" name="salescaseref" value="' + $("#salesref").val() + '"/><input type="hidden" name="stockid" value="' + stkc + '"/><button type="submit" class="btn btn-info abf">ReOrder Item</button></form>';
	html += '<a onclick="removeitem(' + line + ',' + option + ',' + indx + ',\'' + description + '\')" class="panel-action panel-action-dismiss" data-panel-dismiss=""></a>';
	html += '</div>';
	html += '<h2 class="panel-title" style="cursor:pointer" onclick="window.open(\'quotationhistory.php?stockid=' + stkc + '\',\'Ratting\',\'width=550,height=170,0,status=0,scrollbars=1\');">' + stkc + '</h2>';
	html += '</header>';
	html += '<div class="panel-body">';
	html += '<div class="col-md-6">';
	html += '<div class="col-md-2">';
	html += 'Model#:';
	html += '</div>';
	html += '<div class="col-md-9">';
	html += modal;
	html += '</div>';
	html += '<div class="col-md-2">';
	html += 'Part#:';
	html += '</div>';
	html += '<div class="col-md-9">';
	html += part;
	html += '</div>'
	html += '<div class="col-md-2">';
	html += 'Brand:';
	html += '</div>';
	html += '<div class="col-md-9 brandyo">';
	html += bname;
	html += '</div>';
	html += '<br>';
	html += '<div class="col-md-2">';
	html += 'Description:';
	html += '</div>';
	html += '<div class="col-md-9 desc">';
	html += description;
	html += '</div>';
	html += '<div class="col-md-2">';
	html += 'Updated:';
	html += '</div>';
	html += '<div class="col-md-9">';
	html += update;
	html += '</div>';
	html += '</div>';
	html += '<div id="item' + indx + '" class="col-md-6">';
	html += '<div class="col-md-2">';
	html += 'QOH:';
	html += '</div>';
	html += '<div class="col-md-4">';
	html += '<input type="number" value="' + qoh + '" disabled>';
	html += '</div>';
	html += '<div class="col-md-2">';
	html += 'Price:';
	html += '</div>';
	html += '<div class="col-md-4">';
	html += '<input class="sprice" type="number" name="" value="' + sprice + '" disabled>';
	html += '</div>';
	html += '<br>';
	html += '<br>';
	html += '<div class="col-md-2">';
	disupdated = disupdated ? disupdated : "";
	html += 'Discount%:<span style="color: darkred; font-weight: bolder">' + disupdated + '</span>';
	html += '</div>';
	html += '<div class="col-md-4">';
	html += '<input class="discount" type="number" value="' + dis + '">';
	html += '</div>';
	html += '<div class="col-md-2">';
	html += 'UnitRate:';
	html += '</div>';
	html += '<div class="col-md-4">';
	html += '<input class="uprice" type="number" value="' + uprice + '">';
	html += '</div>';
	html += '<br>';
	html += '<br>';
	html += '<div class="col-md-2">';
	html += 'Quantity:';
	html += '</div>';
	html += '<div class="col-md-4">';
	html += '<input class="quantity" type="number" value="' + quantity + '">';
	html += '</div>';
	html += '<div class="col-md-2">';
	html += 'Total:';
	html += '</div>';
	html += '<div class="col-md-4">';
	html += '<input class="total" type="number" value="' + tot + '" disabled>';
	html += '</div>';
	html += '</div>';
	html += '</div>';
	html += '</section>';

	var descItemHTML = "<tr id='opdcd" + indx + "'>";
	descItemHTML += "<td>";
	descItemHTML += modal;
	descItemHTML += "</td>";
	descItemHTML += "<td>";
	descItemHTML += part;
	descItemHTML += "</td>";
	descItemHTML += "<td>";
	descItemHTML += bname;
	descItemHTML += "</td>";
	descItemHTML += "<td>";
	descItemHTML += description;
	descItemHTML += "</td>";
	descItemHTML += "<td class='qohhh'>";
	descItemHTML += qoh;
	descItemHTML += "</td>";
	descItemHTML += "<td class='quantity'>";
	descItemHTML += quantity;
	descItemHTML += "</td>";
	descItemHTML += "<td class='uprice'>";
	descItemHTML += uprice;
	descItemHTML += "</td>";
	descItemHTML += "<td class='discount'>";
	descItemHTML += Math.round(dis) + '%';
	descItemHTML += "</td>";
	descItemHTML += "</tr>";

	$('#opdc' + line + '-' + option + '').append(descItemHTML);

	$('#l' + line + 'o' + option + 'ic').append(html);

	addToBrandsArray(bname);


}

function linetoggle(line) {

	$('#l' + line + 'body').toggleClass('toggleviewnone');

}

function optiontoggle(line, option) {

	$('#l' + line + 'o' + option + 'body').toggleClass('toggleviewnone');

}

function removeitem(line, option, index, description) {

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	swal({
		title: "Are you sure?",
		text: (description) + " : will be removed from the option!",
		type: "warning",
		showCancelButton: true,
		confirmButtonClass: "btn-danger",
		confirmButtonText: "Yes, delete it!",
		showLoaderOnConfirm: true,
		closeOnConfirm: true
	},
		function () {

			NProgress.start();

			$.ajax({
				type: 'POST',
				url: rootpath + "/quotation/api/deleteItem.php",
				data: { itemIndex: index },
				dataType: "json",
				success: function (response) {

					if (response.status == "success") {

						$('#l' + line + 'o' + option + 'i' + response.data.index).remove();

						new PNotify({
							title: 'Success',
							text: "Item ('" + description + "') removed from option!",
							addclass: 'notification-success stack-topleft',
							width: "300px",
							delay: 1000,
							type: 'success'
						});

						items[line + "," + option] -= 1;

						$("#opdcd" + response.data.index + "").remove();

						recalculateoptionboxprice(line, option);

					} else {

						new PNotify({
							title: 'Error',
							text: "Item ('" + description + "') delete failed!",
							addclass: 'notification-error stack-topleft',
							width: "300px",
							delay: 1000,
							type: 'error'
						});

					}

					NProgress.done();

				},
				error: function () {

					new PNotify({
						title: 'Error',
						text: "Item ('" + description + "') delete failed!",
						addclass: 'notification-error stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'error'
					});

					NProgress.done();

				}

			});

		});

}

function removeoption(line, option, voption) {

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	swal({
		title: "Are you sure?",
		text: "All the items under the option will be deleted aswell!",
		type: "warning",
		showCancelButton: true,
		confirmButtonClass: "btn-danger",
		confirmButtonText: "Yes, delete it!",
		showLoaderOnConfirm: true,
		closeOnConfirm: true
	},
		function () {

			NProgress.start();

			$.ajax({
				type: 'POST',
				url: rootpath + "/quotation/api/deleteOption.php",
				data: { line: line, option: option },
				dataType: "json",
				success: function (response) {

					if (response.status == "success") {

						removeoptionCallback(response.data.line, response.data.option);
						new PNotify({
							title: 'Success',
							text: "Option '" + voption + "' deleted successfully!",
							addclass: 'notification-success stack-topleft',
							width: "300px",
							delay: 1000,
							type: 'success'
						});

					} else {

						new PNotify({
							title: 'Error',
							text: "Option '" + voption + "' delete failed!",
							addclass: 'notification-error stack-topleft',
							width: "300px",
							delay: 1000,
							type: 'error'
						});

					}

					NProgress.done();

				},
				error: function () {

					new PNotify({
						title: 'Error',
						text: "Option '" + voption + "' delete failed!",
						addclass: 'notification-error stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'error'
					});

					NProgress.done();

				}

			});

		});

}

function removeoptionCallback(line, option) {
	$('#l' + line + 'o' + option).remove();

	var count = 1;
	$('#l' + line + 'oc > section').each(function () {
		$(this).find('.optionheading').html("Option " + (count++));
	});

	$('#optiondesc' + line + "-" + option).remove();

	var count = 1;
	$('#optionline' + line + 'desccontainer > section').each(function () {
		$(this).find('h4').html("Option No. " + (count++) + " SAH Description");
	});

	voptions[line] -= 1;

}

function removeline(line, vline) {

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	swal({
		title: "Are you sure?",
		text: "All the items & options under the lines will be deleted aswell!",
		type: "warning",
		showCancelButton: true,
		confirmButtonClass: "btn-danger",
		confirmButtonText: "Yes, delete it!",
		closeOnConfirm: true
	},
		function () {

			NProgress.start();

			$.ajax({
				type: 'POST',
				url: rootpath + "/quotation/api/deleteLine.php",
				data: { line: line },
				dataType: "json",
				success: function (response) {

					if (response.status == "success") {

						removelineCallback(response.data.line);
						new PNotify({
							title: 'Success',
							text: "Line '" + vline + "' deleted successfully!",
							addclass: 'notification-success stack-topleft',
							width: "300px",
							delay: 1000,
							type: 'success'
						});

					} else {

						new PNotify({
							title: 'Error',
							text: "Line '" + vline + "' delete failed!",
							addclass: 'notification-error stack-topleft',
							width: "300px",
							delay: 1000,
							type: 'error'
						});

					}

					NProgress.done();
				},
				error: function () {
					new PNotify({
						title: 'Error',
						text: "Line '" + vline + "' delete failed!",
						addclass: 'notification-error stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'error'
					});
					NProgress.done();
				}
			});

		});

}

function removelineCallback(line) {
	$('#l' + line).remove();

	var count = 1;
	$('#linescontainer > section').each(function () {
		$(this).find('.lineheading').html("Line " + (count++));
	});

	$('#linedesc' + line).remove();

	var count = 1;
	$('#linesdescriptioncontainer > section').each(function () {
		$(this).find('h3').html("Line No. " + (count++) + " Client Requirements");
	});

	vlineno -= 1;

}

function inittextboxio(line, optionno, content) {

	var editor = textboxio.replace("#l" + line + "o" + optionno + "desc", {

		codeview: {
			enabled: false,
			showButton: false
		},
		ui: {
			toolbar: {
				items: ["undo", "tools"]
			}
		},
		paste: {
			style: "plain"
		},
		css: {
			stylesheets: ["example.css"]
		}

	});

	editor.content.set(content);

	editor.events.dirty.addListener(function () {

		var de = [];

		var msg = editor.message('warning', 0, 'Content Not Yet Saved.');

		$('.ephox-polish-banner-close').css('display', 'none');

		de['editor'] = editor;
		de['msg'] = msg;
		de['line'] = line;
		de['option'] = optionno;

		if (!(line + "-" + optionno in dirtyEditors))
			dirtyEditors[line + "-" + optionno] = [];

		dirtyEditors[line + "-" + optionno].push(de);

	});

}


$(document.body).on('keyup', 'input.uprice', function () {

	var itemid = $(this).parent().parent().attr('id');
	var discount = $(this).parent().parent().find("div > input.discount").val();
	var uprice = $(this).parent().parent().find("div > input.uprice").val();
	var sprice = $(this).parent().parent().find("div > input.sprice").val();
	var quantity = $(this).parent().parent().find("div > input.quantity").val();

	if (uprice !== uprice || uprice <= 0) {
		$(this).parent().parent().find("div > input.uprice").css("border", "red 2px dotted");
	} else {
		$(this).parent().parent().find("div > input.uprice").css("border", "");
	}

	var dis = (1 - Math.round((uprice / sprice) * 10000000) / 10000000) * 100;

	if (dis !== dis) {
		$(this).parent().parent().find("div > input.discount").css("border", "red 2px dotted");
	} else {
		$(this).parent().parent().find("div > input.discount").css("border", "");
	}

	$(this).parent().parent().find("div > input.discount").val(dis);

	var tot = Math.round(uprice * quantity * 100) / 100;

	if (tot !== tot) {
		$(this).parent().parent().find("div > input.total").css("border", "red 2px dotted");
	} else {
		$(this).parent().parent().find("div > input.total").css("border", "");
	}

	$(this).parent().parent().find("div > input.total").val(tot);

});

$(document.body).on('change', 'input.uprice', function () {

	var reff = $(this);

	recalculateoptionprice(reff);

	var discount = $(this).parent().parent().find("div > input.discount").val();
	var uprice = $(this).parent().parent().find("div > input.uprice").val();
	var sprice = $(this).parent().parent().find("div > input.sprice").val();
	var quantity = $(this).parent().parent().find("div > input.quantity").val();

	if (uprice !== uprice || uprice <= 0) {
		$(this).parent().parent().find("div > input.uprice").css("border", "red 2px dotted");
	} else {

		$(this).parent().parent().find("div > input.uprice").css("border", "2px orange solid");
		$(this).parent().parent().find("div > input.discount").css("border", "2px orange solid");

		var dis = (1 - Math.round((uprice / sprice) * 10000000) / 10000000) * 100;

		$(this).parent().parent().find("div > input.uprice").prop("disabled", true);
		$(this).parent().parent().find("div > input.discount").prop("disabled", true);
		updatePrice($(this).parent().parent().attr("id"), uprice, dis);

		if (dis !== dis) {
			$(this).parent().parent().find("div > input.discount").css("border", "red 2px dotted");
		}

		$(this).parent().parent().find("div > input.discount").val(dis);

		var tot = Math.round(uprice * quantity * 100) / 100;

		if (tot !== tot || tot <= 0) {
			$(this).parent().parent().find("div > input.total").css("border", "red 2px dotted");
		} else {
			$(this).parent().parent().find("div > input.total").css("border", "");
		}

		$(this).parent().parent().find("div > input.total").val(tot);

	}

});

$(document.body).on('keyup', 'input.quantity', function () {

	var uprice = $(this).parent().parent().find("div > input.uprice").val();
	var quantity = $(this).parent().parent().find("div > input.quantity").val();

	var tot = Math.round(uprice * quantity * 100) / 100;

	if (tot !== tot) {
		$(this).parent().parent().find("div > input.total").css("border", "red 2px dotted");
	} else {
		$(this).parent().parent().find("div > input.total").css("border", "");
	}

	$(this).parent().parent().find("div > input.total").val(tot);

	if (quantity !== quantity || quantity <= 0) {
		$(this).parent().parent().find("div > input.quantity").css("border", "red 2px dotted");
	} else {
		$(this).parent().parent().find("div > input.quantity").css("border", "");
	}

});

$(document.body).on('change', 'input.quantity', function () {

	var reff = $(this);

	if (reff.parent().parent().parent().parent().attr("class") == "det")
		return;

	recalculateoptionprice(reff);

	var uprice = $(this).parent().parent().find("div > input.uprice").val();
	var quantity = $(this).parent().parent().find("div > input.quantity").val();

	var tot = Math.round(uprice * quantity * 100) / 100;

	if (tot !== tot) {
		$(this).parent().parent().find("div > input.total").css("border", "red 2px dotted");
	} else {
		$(this).parent().parent().find("div > input.total").css("border", "");
	}

	$(this).parent().parent().find("div > input.total").val(tot);

	if (quantity !== quantity || quantity <= 0) {
		$(this).parent().parent().find("div > input.quantity").css("border", "red 2px dotted");
	} else {

		$(this).parent().parent().find("div > input.quantity").css("border", "2px orange solid");
		$(this).parent().parent().find("div > input.quantity").prop("disabled", true);
		updateQuantity($(this).parent().parent().attr("id"), quantity);

	}

});

$(document.body).on('keyup', 'input.discount', function () {

	var discount = $(this).parent().parent().find("div > input.discount").val();
	var uprice = $(this).parent().parent().find("div > input.uprice").val();
	var sprice = $(this).parent().parent().find("div > input.sprice").val();
	var quantity = $(this).parent().parent().find("div > input.quantity").val();

	if (discount !== discount) {
		$(this).parent().parent().find("div > input.discount").css("border", "red 2px dotted");
	} else {
		$(this).parent().parent().find("div > input.discount").css("border", "");
	}

	var uprice = (sprice) - ((sprice / 100) * discount);

	if (uprice !== uprice) {
		$(this).parent().parent().find("div > input.uprice").css("border", "red 2px dotted");
	} else {
		$(this).parent().parent().find("div > input.uprice").css("border", "");
	}

	$(this).parent().parent().find("div > input.uprice").val(uprice);

	var tot = Math.round(uprice * quantity * 100) / 100;

	if (tot !== tot || tot <= 0) {
		$(this).parent().parent().find("div > input.total").css("border", "red 2px dotted");
	} else {
		$(this).parent().parent().find("div > input.total").css("border", "");
	}

	$(this).parent().parent().find("div > input.total").val(tot);

});

$(document.body).on('change', 'input.discount', function () {

	var reff = $(this);

	var discount = $(this).parent().parent().find("div > input.discount").val();
	var uprice = $(this).parent().parent().find("div > input.uprice").val();
	var sprice = $(this).parent().parent().find("div > input.sprice").val();
	var quantity = $(this).parent().parent().find("div > input.quantity").val();

	if (discount !== discount || discount >= 100) {
		$(this).css("border", "red 2px dotted");
	} else {

		$(this).css("border", "2px orange solid");
		$(this).parent().parent().find("div > input.uprice").css("border", "2px orange solid");

		recalculateoptionprice(reff);

		var uprice = (sprice) - ((sprice / 100) * discount);

		$(this).parent().parent().find("div > input.uprice").prop("disabled", true);
		$(this).parent().parent().find("div > input.discount").prop("disabled", true);
		updatePrice($(this).parent().parent().attr("id"), uprice, discount);

		if (uprice !== uprice) {
			$(this).parent().parent().find("div > input.uprice").css("border", "red 2px dotted");
		}

		$(this).parent().parent().find("div > input.uprice").val(uprice);

		var tot = Math.round(uprice * quantity * 100) / 100;

		if (tot !== tot || tot <= 0) {
			$(this).parent().parent().find("div > input.total").css("border", "red 2px dotted");
		} else {
			$(this).parent().parent().find("div > input.total").css("border", "");
		}

		$(this).parent().parent().find("div > input.total").val(tot);

	}

});

function updateQuantity(item, quantity) {
	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/updateItem.php",
		data: { orderno: order, salescaseref: salesref, name: "quantity", value: quantity, item: item },
		dataType: "json",
		success: function (response) {
			var status = response.status;

			if (status == "success") {

				$("#" + item + "").find("input.quantity").css("border", "2px green solid");
				$("#" + item + "").find("input.quantity").val(response.data.value);
				var id = item.split("item")[1];
				$("#opdcd" + id + "").find(".quantity").html(response.data.value);
				$("#" + item + "").find("input.quantity").prop("disabled", false);

			} else {

				$("#" + item + "").find("input.quantity").css("border", "2px red solid");
				$("#" + item + "").find("input.quantity").prop("disabled", false);

			}
		},
		error: function () {

			$("#" + item + "").find("input.quantity").css("border", "2px red solid");
			$("#" + item + "").find("input.quantity").prop("disabled", false);

		}

	});

}

function updatePrice(item, uprice, discount) {

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/updateItem.php",
		data: { orderno: order, salescaseref: salesref, name: "uprice", value: uprice, item: item, discount: discount },
		dataType: "json",
		success: function (response) {
			var status = response.status;

			if (status == "success") {

				$("#" + item + "").find("input.uprice").css("border", "2px green solid");
				$("#" + item + "").find("input.uprice").val(response.data.value);
				$("#" + item + "").find("input.uprice").prop("disabled", false);

				$("#" + item + "").find("input.discount").css("border", "2px green solid");
				$("#" + item + "").find("input.discount").val((response.data.discount) * 100);
				$("#" + item + "").find("input.discount").prop("disabled", false);

				var id = item.split("item")[1];
				$("#opdcd" + id + "").find(".uprice").html(response.data.value);
				$("#opdcd" + id + "").find(".discount").html(((response.data.discount) * 100) + "%");

			} else {

				$("#" + item + "").find("input.uprice").css("border", "2px red solid");
				$("#" + item + "").find("input.uprice").prop("disabled", false);

				$("#" + item + "").find("input.discount").css("border", "2px red solid");
				$("#" + item + "").find("input.discount").prop("disabled", false);

			}

		},
		error: function () {

			$("#" + item + "").find("input.uprice").css("border", "2px red solid");
			$("#" + item + "").find("input.uprice").prop("disabled", false);

			$("#" + item + "").find("input.discount").css("border", "2px red solid");
			$("#" + item + "").find("input.discount").prop("disabled", false);

		}

	});

}

$(document).ready(function () {

	table = $('#searchresults').DataTable({
		"columns": [
			{ "data": "stockid" },
			{ "data": "mnfcode" },
			{ "data": "mnfpno" },
			{ "data": "qoh" },
			{ "data": "is" },
			{ "data": "mname" },
			{ "data": "description" },
			{ "data": "action" },
		]
	});

});

$("#bss").click(function (e) {
	e.preventDefault();

	NProgress.start();


	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	var code = $("#scode").val();
	var cat = $("#scat").val();

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/searchtemp.php",
		data: { StockCode: code, StockCat: cat },
		dataType: "json",
		success: function (response) {

			var status = response.status;

			table.clear().draw();
			table.rows.add(response.data).draw();

			NProgress.done();

		},
		error: function () {

			NProgress.done();

		}

	});

});

$("#bcr").click(function (e) {
	e.preventDefault();

	NProgress.start();

	table.clear().draw();

	NProgress.done();

});

function insertitem(itemindex, brand) {

	NProgress.start();

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	var line = selectedLine;
	var option = selectedOption;

	$('button').prop('disabled', true);

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/addNewItem.php",
		data: { orderno: order, salescaseref: salesref, line: line, option: option, item_id: itemindex },
		dataType: "json",
		success: function (response) {

			var status = response.status;

			if (status == "success") {

				var d = response.data;

				additemCallback(d.id, d.line, d.option, d.title, brand, d.desc, d.quantity, d.price, d.price, d.total, d.qoh, d.discount, d.update, d.model, d.part);

				var html = "<tr>";
				html += "<td>";
				html += d.title;
				html += "</td>";
				html += "<td>";
				html += brand;
				html += "</td>";
				html += "<td>";
				html += d.desc;
				html += "</td>";
				html += "</tr>";

				$("#cicis").append(html);

				new PNotify({
					title: 'Success',
					text: '' + d.desc + '\n Added successfully .',
					addclass: 'notification-success stack-topleft',
					width: "300px",
					delay: 1000,
					type: 'success'
				});

			} else {

				new PNotify({
					title: 'Warning',
					text: response.message,
					addclass: 'notification-warning stack-topleft',
					width: "300px",
					delay: 1000,
					type: 'warning'
				});

			}

			$('button').prop('disabled', false);
			NProgress.done();

		},
		error: function () {

			new PNotify({
				title: 'Error',
				text: 'Item could not be added.',
				addclass: 'notification-error stack-topleft',
				width: "300px",
				delay: 1000,
				type: 'error'
			});

			$('button').prop('disabled', false);
			NProgress.done();

		}

	});

}

function insertitemManually(itemindex, brand, line, option, qty, uprice, dis, tot) {

	NProgress.start();
	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();
	var qty = qty;
	var uprice = uprice;
	var dis = dis;
	var tot = tot;
	var line = line;
	var option = option;

	$('button').prop('disabled', true);

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/addNewItem.php",
		data: { orderno: order, salescaseref: salesref, line: line, option: option, item_id: itemindex },
		dataType: "json",
		success: function (response) {

			var status = response.status;
			if (status == "success") {
				var d = response.data;
				
				additemCallback(d.id, d.line, d.option, d.title, brand, d.desc, qty, d.price, uprice, tot, d.qoh, dis, d.update, d.model, d.part);
				var val = "item";
				var item = val + d.id;
				updateQuantity(item, qty);
				updatePrice(item, uprice, dis);
				$(".descinfo").trigger("change");

				var html = "<tr>";
				html += "<td>";
				html += d.title;
				html += "</td>";
				html += "<td>";
				html += brand;
				html += "</td>";
				html += "<td>";
				html += d.desc;
				html += "</td>";
				html += "</tr>";

				$("#cicis").append(html);

				new PNotify({
					title: 'Success',
					text: '' + d.desc + '\n Added successfully .',
					addclass: 'notification-success stack-topleft',
					width: "300px",
					delay: 1000,
					type: 'success'
				});

			} else {

				new PNotify({
					title: 'Warning',
					text: response.message,
					addclass: 'notification-warning stack-topleft',
					width: "300px",
					delay: 1000,
					type: 'warning'
				});

			}

			$('button').prop('disabled', false);
			NProgress.done();

		},
		error: function () {

			new PNotify({
				title: 'Error',
				text: 'Item could not be added.',
				addclass: 'notification-error stack-topleft',
				width: "300px",
				delay: 1000,
				type: 'error'
			});

			$('button').prop('disabled', false);
			NProgress.done();

		}

	});

}


$(document.body).on('keyup', '.order-detailss', function () {
	$(this).css("border", "");
});

$(document.body).on('change', '.order-detailss', function () {

	NProgress.start();

	var ref = $(this);

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	var name = $(this).attr("name");
	var value = $(this).val();

	if (name == "services" || name == "umqd") {
		value = ($(this).is(":checked")) ? 1 : 0;
	}


	$(this).prop("disabled", true);

	ref.css("border", "2px orange solid");

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/updateOrderDetails.php",
		data: { orderno: order, salescaseref: salesref, name: name, value: value },
		dataType: "json",
		success: function (response) {

			var status = response.status;

			if (status == "success") {

				ref.css("border", "2px green solid");

			} else {

				ref.css("border", "2px red solid");

			}

			ref.prop("disabled", false);

			NProgress.done();

		},
		error: function () {

			ref.prop("disabled", false);
			ref.css("border", "2px red solid");
			NProgress.done();

		}

	});


});

$(document.body).on('change', '.descinfo', function () {

	NProgress.start();

	var ref = $(this);

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	var name = $(this).attr("name");
	var value = $(this).val();

	var option = $(this).parent().parent().parent().parent().parent().parent().attr("id").split("-")[1];

	if (name == "services") {
		value = ($(this).is(":checked")) ? 1 : 0;
	}
	if (name == "printexchange") {
		value = ($(this).is(":checked")) ? 1 : 0;
	}

	$(this).prop("disabled", true);

	ref.css("border", "2px orange solid");

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/updateOption.php",
		data: { orderno: order, salescaseref: salesref, name: name, value: value, option: option },
		dataType: "json",
		success: function (response) {

			var status = response.status;

			if (status == "success") {

				ref.css("border", "2px green solid");
				updateoptionquantity(ref, name, value);

			} else {

				ref.css("border", "2px red solid");

			}

			ref.prop("disabled", false);

			NProgress.done();

		},
		error: function () {

			ref.prop("disabled", false);
			ref.css("border", "2px red solid");
			NProgress.done();

		}

	});

});

$("#erbtn").click(function (e) {

	e.preventDefault();

	NProgress.start();

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();

	$("#epreviewcontainer").html("");

	$.ajax({
		type: 'GET',
		url: rootpath + "/PDFQuotationIP.php?QuotationNo=" + order + "&root=" + rootpath,
		dataType: "json",
		success: function (response) {

			var ihtml = "<iframe ";
			ihtml += 'id="previewframe" ';
			ihtml += 'src = "ViewerJS/#../tempPDF/' + order + 'ext.pdf" ';
			ihtml += "width='100%' height='1024'";
			ihtml += ' allowfullscreen webkitallowfullscreen></iframe>';

			$("#epreviewcontainer").html(ihtml);

			NProgress.done();

		},
		error: function () {

			NProgress.done();

		}

	});

});

$("#irbtn").click(function (e) {
	e.preventDefault();

	NProgress.start();

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	$("#ipreviewcontainer").html("");

	$.ajax({
		type: 'GET',
		url: rootpath + "/PDFQuotationIPint.php?QuotationNo=" + order + "&root=" + rootpath,
		dataType: "json",
		success: function (response) {

			console.log(response);

			if (response.status == "success") {
				var ihtml = "<iframe ";
				ihtml += 'id="previewframe" ';
				ihtml += 'src = "ViewerJS/#../tempPDF/' + order + 'int.pdf" ';
				ihtml += "width='100%' height='1024'";
				ihtml += ' allowfullscreen webkitallowfullscreen></iframe>';

				$("#ipreviewcontainer").html(ihtml);
			}

			NProgress.done();

		},
		error: function () {

			NProgress.done();

		}

	});

});

$("#checkforwarnings").click(function (e) {

	NProgress.start();

	var ref = $(this);

	ref.prop("disabled", true);

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/checkforwarnings.php",
		data: { salescaseref: salesref, orderno: order },
		dataType: "json",
		success: function (r) {

			$("#warningscontainer").html("");
			if (r.status == "bypass") {

				savequotation(ref, rootpath, salesref, order, r.formid);

			} else if (r.elines == 0 && r.eoptions == 0 && r.itemsuo == 0 && r.items == 0 && r.options == 0 && r.lines != 0 && r.itemswq > 0) {

				savequotation(ref, rootpath, salesref, order, r.formid);

			} else {

				var html = '<div class="alert alert-danger">';

				if (r.status == "error") {

					window.location = "" + rootpath + "/salescase.php/?salescaseref=" + salesref + "";
					return;
				}

				if (r.lines == 0) {

					html += '<strong>';
					html += 'No lines added in quotation.';
					html += '</strong>';
					html += '</br>';

				}

				if (r.elines != 0) {

					html += '<strong>';
					html += r.elines;
					html += ' Empty Lines Found.';
					html += '</strong>';
					html += '</br>';

				}

				if (r.eoptions != 0) {

					html += '<strong>';
					html += r.eoptions;
					html += ' Empty Options Found.';
					html += '</strong>';
					html += '</br>';

				}

				if (r.options != 0) {

					html += '<strong>';
					html += r.options;
					html += ' Options Found with 0 Quantity.';
					html += '</strong>';
					html += '</br>';

					html += '<strong>';
					html += r.itemsuo;
					html += ' Items exist under options with 0 quantity.';
					html += '</strong>';
					html += '</br>';

				}

				if (r.items != 0) {

					html += '<strong>';
					html += r.items;
					html += ' Items Found with 0 quantity.';
					html += '</strong>';
					html += '</br>';

				}

				if (r.itemswq == 0) {

					html += '<strong>';
					html += 'Atleast 1 item needs to be added with quantity > 0';
					html += '</strong>';
					html += '</br>';

				}

				html += '</div>';

				if (r.lines != 0 && r.itemswq > 0) {
					//$("#proceedanyway").css("display","block");
				}
				$("#warningscontainer").html(html);
				NProgress.done();
				ref.prop("disabled", false);

			}

			//<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

		},
		error: function () {

			$("#warningscontainer").html("");

			var html = '<div class="alert alert-danger">';
			html += "<strong>";
			html += "Request Could Not Be Completed for some reason.";
			html += "</strong>";
			html += "</div>";

			$("#warningscontainer").html(html);

			ref.prop("disabled", false);
			NProgress.done();

		}

	});

});

function savequotation(ref, rootpath, salesref, orderno, formid) {



	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/saveFinalQuotation.php",
		data: { salescaseref: salesref, orderno: orderno, FormID: formid },
		dataType: "json",
		success: function (r) {

			NProgress.done();

			if (r.status != "error") {
				window.location = "" + rootpath + "/salescase.php/?salescaseref=" + salesref + "";
			}

			ref.prop("disabled", false);

		},
		error: function () {

			$("#warningscontainer").html("");

			var html = '<div class="alert alert-danger">';
			html += "<strong>";
			html += "Quotation Save Failed.";
			html += "</strong>";
			html += "</div>";

			$("#warningscontainer").html(html);

			ref.prop("disabled", false);
			NProgress.done();

		}

	});

}

function updateoptionquantity(ref, name, value) {

	if (name != "quantity") {
		return;
	}

	var itid = ref.parent().parent().parent().parent().parent().parent().attr('id');
	var tids = (itid.split("optiondesc")[1].split('-'));
	ref.parent().parent().find(".tpricecont").html(Math.round(Number(ref.parent().parent().find(".pricecont").html()) * value * 100) / 100);
	$("#lt" + tids[0] + "ot" + tids[1] + "").html(Math.round(Number(ref.parent().parent().find(".pricecont").html()) * value * 100) / 100);

	updateQuotationValue();

}

function recalculateoptionprice(ref) {

	var parent = ref.parent().parent().parent().parent().parent();
	var parrentid = parent.attr("id");
	var total = 0;

	var line = parrentid.split("o")[0].split("l")[1];
	var option = parrentid.split("o")[1].split("ic")[0];


	parent.find("section").each(function () {

		var item = $(this);

		total += Number(item.find(".total").val());

	});

	$("#optiondesc" + line + "-" + option + "").find(".pricecont").html(total);

	var quantity = $("#optiondesc" + line + "-" + option + "").find(".det").find(".quantity").val();
	var subtotal = quantity * total;

	subtotal = Math.round(subtotal * 100) / 100;

	$("#optiondesc" + line + "-" + option + "").find(".tpricecont").html(subtotal);
	$("#lt" + line + "ot" + option + "").html(subtotal);

	updateQuotationValue();

}

function recalculateoptionboxprice(line, option) {

	var parent = $("#l" + line + "o" + option + "ic");
	var parrentid = parent.attr("id");
	var total = 0;

	var line = parrentid.split("o")[0].split("l")[1];
	var option = parrentid.split("o")[1].split("ic")[0];


	parent.find("section").each(function () {

		var item = $(this);

		total += Number(item.find(".total").val());

	});

	$("#optiondesc" + line + "-" + option + "").find(".pricecont").html(total);

	var quantity = $("#optiondesc" + line + "-" + option + "").find(".det").find(".quantity").val();
	var subtotal = quantity * total;

	subtotal = Math.round(subtotal * 100) / 100;

	$("#optiondesc" + line + "-" + option + "").find(".tpricecont").html(subtotal);
	$("#lt" + line + "ot" + option + "").html(subtotal);

	updateQuotationValue();


}

function addToBrandsArray(bname) {

	brandsArray[bname] = 0;

	$('#brandslist').html('');

	for (var prop in brandsArray) {
		if (brandsArray.hasOwnProperty(prop)) {
			addBrandDiscountEntries(prop);
		}
	}

}

function addBrandDiscountEntries(brandname) {

	html = '<div class="branditem col-md-12" style="margin-top:10px">';
	html += '<div class="col-md-3 brandname">' + brandname + '</div>';
	html += '<div class="col-md-9">';
	html += '<input type="number" name="discountpercent" class="discountpercent" style="border: 1px solid #424242" value="0">';
	html += '</div>';
	html += '</div>';

	$('#brandslist').append(html);
}

$("#updategroupdiscountsbutton").on('click', function () {

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	var groupDisArr = {};

	var ref = $(this);
	ref.prop('disabled', true);

	$('#brandslist').find('.branditem').each(function () {

		var bnm = "";
		var per = 0;

		bnm = $(this).find('.brandname').html();
		per = $(this).find('.discountpercent').val();

		groupDisArr[bnm] = per;

	});

	$.ajax({
		type: 'POST',
		url: rootpath + '/quotation/api/groupDiscount.php',
		data: { data: groupDisArr, order: order, salesref: salesref },
		success: function (res) {
			location.reload();
		},
		error: function () {
			location.reload();
		}
	})

})

function updateQuotationValue() {

	var quotTotal = 0;

	$('.lineoptiontotal').each(function () {
		quotTotal += Number($(this).html());
	})

	$('#totalquotationvalue').html(quotTotal);

	let overLimit = "";
	if ((window.currentCredit + quotTotal) > window.creditLimit) {
		$('#totalquotationvalue').css("color", "red");
		overLimit = ` ( ${window.creditLimit - (window.currentCredit + quotTotal)} Over Credit Limit)`;
	} else {
		$('#totalquotationvalue').css("color", "#424242");
		overLimit = `${window.creditLimit - (window.currentCredit + quotTotal)}`;
	}


	let statementLink = `
		<form id="printStatementForm" action="/sahamid/reports/balance/custstatement/../../../customerstatement.php" method="post" target="_blank">
			<input type="hidden" name="FormID" value="${window.formID}">
			<input type="hidden" name="cust" value="${window.debtorno}">
			<input type="submit" value="Customer Statement" class="btn-info" style="padding: 8px; border:1px #424242 solid;">
		</form>
	`
	let html = "";
	html += `<center><table border="2" style="font-size: large;"><tr><td> &nbsp;Total Outstanding &nbsp;</td><td>&nbsp; Document Total&nbsp; </td><td> &nbsp;Credit Remaining &nbsp;</td></tr>` +
		`<tr><td>${(Math.round(window.totalOutstanding).toLocaleString())}</td><td>${(Math.round(quotTotal).toLocaleString())}</td><td>${(overLimit)}</td></tr></table></center><br/>`;
	html += `<center>${(statementLink)}</center>`;
	//	$('#totalquotationvalue').html(`<table>Total Outstanding (${(Math.round(window.totalOutstanding).toLocaleString())}) Document Total: `+Math.round(quotTotal).toLocaleString()+overLimit.toLocaleString());
	if (window.flag != "on") {
		$('#totalquotationvalue').html(html);
	}

}

$(document.body).on("mouseenter", ".panel-featured-info .panel-heading", function () {

	let ref = $(this);
	if (ref.hasClass("donedonadone") || ref.hasClass("requestinprogress"))
		return;

	$(".panel-featured-info").each(function () {

		let ref = $(this).find(".panel-heading");
		let stockid = $(this).find(".panel-title").html();

		if (ref.hasClass("donedonadone") || ref.hasClass("requestinprogress"))
			return;

		ref.addClass("requestinprogress");

		let itemParent = ref.parent();
		let itemIndex = itemParent.attr("id").split("i")[1];

		$.get("quotation/api/getTotalItemQOH.php?stockid=" + stockid, function (res, status) {
			res = JSON.parse(res);
			if (res.status == "success") {
				ref.removeClass("requestinprogress");
				ref.addClass("donedonadone");

				$("#opdcd" + itemIndex).find(".qohhh").css("text-align", "left");
				$("#opdcd" + itemIndex).find(".qohhh").html("&nbsp;^| &nbsp;" + res.qoh);
				ref.find(".panel-title").html(ref.find(".panel-title").html() + " (Total QOH: " + res.qoh + ", IS QTY: " + res.is + ")");
			}
		});

	});

	/*let ref = $(this);
	let stockid = $(this).find(".panel-title").html();

	if(ref.hasClass("donedonadone") || ref.hasClass("requestinprogress"))
		return;

	ref.addClass("requestinprogress");
	
	let itemParent = ref.parent();
	
	let itemIndex = itemParent.attr("id").split("i")[1];
	

	$.get("quotation/api/getTotalItemQOH.php?stockid="+stockid, function(res, status){
		res = JSON.parse(res);
		if(res.status == "success"){
			ref.removeClass("requestinprogress");
			ref.addClass("donedonadone");
			
			$("#opdcd"+itemIndex).find(".qohhh").css("text-align","left");
			$("#opdcd"+itemIndex).find(".qohhh").html("&nbsp;^| &nbsp;"+res.qoh);
			ref.find(".panel-title").html(ref.find(".panel-title").html()+" (Total QOH: "+res.qoh+")");
		}
	});*/

});

$(document.body).on("click", ".copyto", function () {

	let ref = $(this);

	let line = ref.parent().parent().attr("id").split("optiondesc")[1].split("-")[0];
	let option = ref.parent().parent().attr("id").split("optiondesc")[1].split("-")[1];

	let editor1 = textboxio.get(`#l${line}o${option}desc`);

	let description = "";

	$(`#opdc${line}-${option}`).find("tr").each(function () {

		let model = $(this).find("td:nth(0)").html();
		let brand = $(this).find("td:nth(2)").html();
		let desc = $(this).find("td:nth(3)").html();

		description += desc + "<br>";
		description += "Model: " + model + "<br>";
		description += "Make: " + brand + "<br>";

	});

	editor1[0].content.set(description);
	editor1[0].content.setDirty(true);

});

function insertWillQuoteLater(line, option) {

	NProgress.start();

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	let itemindex = "3064WQLATEWillQuoteLater";
	let brand = "Local ";

	$('button').prop('disabled', true);

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/addNewItem.php",
		data: { orderno: order, salescaseref: salesref, line: line, option: option, item_id: itemindex },
		dataType: "json",
		success: function (response) {

			var status = response.status;

			if (status == "success") {

				var d = response.data;

				additemCallback(d.id, d.line, d.option, d.title, brand, d.desc, d.quantity, d.price, d.price, d.total, d.qoh, d.discount, d.update, d.model, d.part);

				var html = "<tr>";
				html += "<td>";
				html += d.title;
				html += "</td>";
				html += "<td>";
				html += brand;
				html += "</td>";
				html += "<td>";
				html += d.desc;
				html += "</td>";
				html += "</tr>";

				$("#cicis").append(html);

				new PNotify({
					title: 'Success',
					text: '' + d.desc + '\n Added successfully .',
					addclass: 'notification-success stack-topleft',
					width: "300px",
					delay: 1000,
					type: 'success'
				});

			} else {

				new PNotify({
					title: 'Warning',
					text: response.message,
					addclass: 'notification-warning stack-topleft',
					width: "300px",
					delay: 1000,
					type: 'warning'
				});

			}

			$('button').prop('disabled', false);
			NProgress.done();

		},
		error: function () {

			new PNotify({
				title: 'Error',
				text: 'Item could not be added.',
				addclass: 'notification-error stack-topleft',
				width: "300px",
				delay: 1000,
				type: 'error'
			});

			$('button').prop('disabled', false);
			NProgress.done();

		}

	});

}

function insertRegretItem(line, option) {

	NProgress.start();

	var order = $('#orderno').val();
	var rootpath = $('#rootpath').val();
	var salesref = $('#salesref').val();

	let itemindex = "3061REGRETREGRET";
	let brand = "Local ";

	$('button').prop('disabled', true);

	$.ajax({
		type: 'POST',
		url: rootpath + "/quotation/api/addNewItem.php",
		data: { orderno: order, salescaseref: salesref, line: line, option: option, item_id: itemindex },
		dataType: "json",
		success: function (response) {

			var status = response.status;

			if (status == "success") {

				var d = response.data;

				additemCallback(d.id, d.line, d.option, d.title, brand, d.desc, d.quantity, d.price, d.price, d.total, d.qoh, d.discount, d.update, d.model, d.part);

				var html = "<tr>";
				html += "<td>";
				html += d.title;
				html += "</td>";
				html += "<td>";
				html += brand;
				html += "</td>";
				html += "<td>";
				html += d.desc;
				html += "</td>";
				html += "</tr>";

				$("#cicis").append(html);

				new PNotify({
					title: 'Success',
					text: '' + d.desc + '\n Added successfully .',
					addclass: 'notification-success stack-topleft',
					width: "300px",
					delay: 1000,
					type: 'success'
				});

			} else {

				new PNotify({
					title: 'Warning',
					text: response.message,
					addclass: 'notification-warning stack-topleft',
					width: "300px",
					delay: 1000,
					type: 'warning'
				});

			}

			$('button').prop('disabled', false);
			NProgress.done();

		},
		error: function () {

			new PNotify({
				title: 'Error',
				text: 'Item could not be added.',
				addclass: 'notification-error stack-topleft',
				width: "300px",
				delay: 1000,
				type: 'error'
			});

			$('button').prop('disabled', false);
			NProgress.done();

		}

	});

}

$(document.body).on("change", ".uomoption", function () {

	let ref = $(this);

	let order = $('#orderno').val();
	let rootpath = $('#rootpath').val();
	let salesref = $('#salesref').val();

	let name = $(this).attr("name");
	let value = $(this).val();

	let option = $(this).attr("data-option");

	ref.css("border", "2px orange solid");

	$.post(rootpath + "/quotation/api/updateOption.php",
		{
			orderno: order,
			salescaseref: salesref,
			name: name, value: value,
			option: option
		}, function (res, status, something) {

			res = JSON.parse(res);

			if (res.status == "success") {
				ref.css("border", "2px green solid");
			} else {
				ref.css("border", "2px red solid");
			}

		});

});

$(document.body).on("change", ".optionPrice", function () {

	let ref = $(this);

	let order = $('#orderno').val();
	let rootpath = $('#rootpath').val();
	let salesref = $('#salesref').val();

	let name = $(this).attr("name");
	let value = $(this).val();

	let option = $(this).attr("data-option");

	ref.css("border", "2px orange solid");

	$.post(rootpath + "/quotation/api/updateOption.php",
		{
			orderno: order,
			salescaseref: salesref,
			name: name, value: value,
			option: option
		}, function (res, status, something) {

			res = JSON.parse(res);

			if (res.status == "success") {
				ref.css("border", "2px green solid");
			} else {
				ref.css("border", "2px red solid");
			}

		}
	);

});

$("#updateQuoteRateValidityPrices").on("click", function () {

	let order = $('#orderno').val();
	let rootpath = $('#rootpath').val();
	let salesref = $('#salesref').val();

	$.post(rootpath + "/quotation/api/updateRateClausePrice.php",
		{
			orderno: order,
			salescaseref: salesref
		}, function (res, status, something) {
			res = JSON.parse(res);
			if (res.status == "success") {
				alert("Prices Updated Successfully")
			}
		}
	);
});

