	var dirtyEditors = [];
	var table = null;
	var selectedLine = 0;
	var selectedOption = 0;
	var stack_topleft = {"dir1": "down", "dir2": "right", "push": "top"};
	NProgress.start();
	//Document Ready
	$(document).ready(function(){

		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();
		$('#rendering').addClass("rendering");

		$.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/retrieveQuotation.php",
		    data: {orderno: order},
		    dataType: "json",
		    success: function(response) { 
		    	var status = response.status;

		    	if(status == "success"){
		    		var data = response.data;
		    		var lines = data.lines;
		    		
		    		$('#clientnamebasic').html(data.name);
		    		$('#clientlocationbasic').html(data.locationname);
		    		$('#salesman').html(data.salesmann);
		    		if(data.existing == 1)
		    			$('#eorderno').html(data.eorderno);
		    		else
		    			$('#eorderno').html("New Quotation");
		    			
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
		    		$("#order-details").find(".fromstkloc").val(data.fromstkloc);
		    		$("#order-details").find(".gst").val(data.gst);
		    		$("#order-details").find(".WHT").val(data.WHT);
		    		$("#order-details").find(".comments").val(data.comments);

		    		for (var index in lines) {

		    			NProgress.inc();

					    if (!lines.hasOwnProperty(index)) {
					        continue;
					    }

					    var lineid = lines[index].lineindex;
					    var creq   = lines[index].clientrequirements;
					    addlineCallback(lineid, creq);

					    var options = lines[index].options;

					    for(var optionIndex in options){

					    	NProgress.inc();

					    	if (!options.hasOwnProperty(optionIndex)) {
						        continue;
						    }

						    var lineno 	  = options[optionIndex].lineno;
						    var optionno  = options[optionIndex].optionindex;
						    var opttext   = options[optionIndex].optiontext;
						    var stkstatus = options[optionIndex].stockstatus;
						    var quanty 	  = options[optionIndex].quantity;

						    addoptionCallback(lineno,optionno,opttext,stkstatus,quanty);

						    var items = options[optionIndex].items;
						    var pricetot = 0;

						    for(var itemIndex in items){

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
							    //var uprice = items[itemIndex].unitprice;
							    var qohal = items[itemIndex].qohatloc;
							    var dis = items[itemIndex].discountpercent;
							    var ud = items[itemIndex].lastcostupdate;
							    var ub = items[itemIndex].lastupdatedby;
							    var modal = items[itemIndex].mnfcode;
							    var part = items[itemIndex].mnfpno;

							    var update = ub+"("+ud+")"; 
								var uprice = 0;

								dis = dis*100;

							    if(dis != 0){
							    	uprice = Math.round((sprice*100)-(((sprice/100)*dis)*100))/100;
							    }else
							    	uprice = sprice;

							    var tot = qty*uprice;

							    pricetot += tot;

							    additemCallback(indx,ln,on,stkc,bname,des,qty,sprice,uprice,tot,qohal,dis,update,modal,part);

							}

							pricetot = Math.round(pricetot*100)/100;
							subtotal = Math.round(pricetot*quanty*100)/100;

							$("#optiondesc"+lineno+"-"+optionno+"").find(".pricecont").html(pricetot);
							$("#optiondesc"+lineno+"-"+optionno+"").find(".tpricecont").html(subtotal);

					    }

					}

					NProgress.set(0.7);
					
		    		$('#rendering').removeClass("rendering");
		    		$('#rendering').css("display","none");
		    		NProgress.done();

		    	}else{

					NProgress.set(0.5);

	    			$('#rendering').removeClass("rendering");
		    		$('#rendering').html("<h1>"+response.message+"</h1>");
			    
		    		NProgress.done();
			    
		    	}

		    	
		    },
		    error: function(){
		    	swal("Error","Some error occured when fetching the quotation.","error");
		    	$('#rendering').removeClass("rendering");
	    		NProgress.done();	
		    }

		});

		setInterval(function(){
			checkDirtyAndUpdate();
		}, 15000);
	
	});


	
	//Description Update Save
	function checkDirtyAndUpdate(){
		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();

		for(index in dirtyEditors){

			if(dirtyEditors[index].length > 0){

				var dEditor = dirtyEditors[index].pop();
				dirtyEditors[index].push(dEditor);

				var text = dEditor.editor.content.get();

				var msg = dEditor.msg;
				var editor = dEditor.editor;
				var line = dEditor.line;

				var option = dEditor.option;
				var type   = "";

				if(option == 0)
					type = "cr";
				else
					type = "desc";

				if(dEditor.editor.content.isDirty()){

					dEditor.editor.content.setDirty(false);

					$.ajax({
						type: 'POST',
					    url: rootpath+"/quotation/api/saveDescription.php",
					    data: {orderno: order,lineno: line, description: text, type: type,index: index,option: option},
					    dataType: "json",
					    success: function(response) { 
					    	var status = response.status;

				    		if(status == "success"){

				    			var data = response.data;
					    		saveDescriptionCallback(data.index);
					    	
					    	}

					    },
					    error: function(){
					    	alert("Save Error");
					    }
					});

				}
			}

		}
		
	}

	function saveDescriptionCallback(index){

		var dedit = dirtyEditors[index].shift();

		var msg = dedit.msg;
		var editor = dedit.editor;

		msg.hide();
		editor.message('info',3000,"Saved!");
	}

	//--------------

	
	$('#closesearchoverlay').click(function(){
		$('#searchoverlay').css('display','none');
		table.clear().draw();
		$("#cicis").html("");
		$("#scat").val("All");
		$("#scode").val("");
	});

	function addline(){
		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();
		NProgress.start();
		$.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/createNewLine.php",
		    data: {orderno: order, salescaseref: salesref},
		    dataType: "json",
		    success: function(response) { 
		    	var status = response.status;
		    	
		    	if(status == "success"){
		    		var data = response.data;
		    		var line = data.line_id;
		    		var req  = "";
		    		addlineCallback(line,req);
		    	}else{
		    	
		    		swal("Error","Save Error","error");

		    	}
		    	NProgress.done();
		    },
		    error: function(){
		    	swal("Error","Save Error","error");
		    	NProgress.done();
		    }
		});
	}

	function addlineCallback(line, requirements){

		var vline = vlineno += 1;
		options[line] = 0;
		voptions[line] = 0;

		var html = '<section id="l'+line+'" class="panel panel-featured">';
		html += '<header class="panel-heading">';
		html += '<div class="panel-actions">';
		html += '<a onclick="linetoggle('+line+')" class="panel-action panel-action-toggle" ></a>';
		html += '<a onclick="removeline('+line+','+vline+')" class="panel-action panel-action-dismiss" data-panel-dismiss=""></a>';
		html += '</div>';
		html += '<h2 class="panel-title lineheading">Line '+vline+'</h2>';
		html += '</header>';
		html += '<div id="l'+line+'body" class="panel-body">';
		html += '<div id="l'+line+'oc">';
		html += '</div>';
		html += '<div id="l'+line+'oa" class="pull-right">';
		html += '<button type="button" class="btn btn-primary" onclick="addoption('+line+')" name="button">Add New Option</button>';
		html += '</div>';
		html += '</div>';
		html += '</section>';
		
		var descHTML = '<section class="line" id="linedesc'+line+'" style="text-align:center; margin: 0 auto;">';
		descHTML += '<h3>Line No. '+vline+' Client Requirements </h3>';
		descHTML += '<textarea  style="width: 100%; height: 100%;" id = "l'+line+'o0desc" cols="90" rows="10"></textarea>';
		descHTML += '<div id="optionline'+line+'desccontainer" style="text-align:center; margin: 0 auto;"></div>';
		descHTML += '</section>';
		
		$('#linesdescriptioncontainer').append(descHTML);
		
		inittextboxio(line,0,requirements);

		$('#linescontainer').append(html);
	}

	function addoption(line){

		NProgress.start();

		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();
		$.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/createNewOption.php",
		    data: {orderno: order, salescaseref: salesref, lineno: line},
		    dataType: "json",
		    success: function(response) { 
		    	var status = response.status;
		    	
		    	if(status == "success"){
		    		var data = response.data;
		    		var line = data.line_id;
		    		var option = data.option_id;
		    		var req  = "";
		    		addoptionCallback(line,option,req,"",0);
		    	}else{
		    		
		    		swal("Error","Save Error","error");

		    	}
		    	NProgress.done();
		    },
		    error: function(){
		    	NProgress.done();
		    	swal("Error","Save Error","error");
		    }
		});

	}

	function addoptionCallback(line,option, description,stockstatus, quantity){

	    var voption = voptions[line] += 1;

		items[line+","+option] = 0;

	    var html = '<section id="l'+line+'o'+option+'" class="panel panel-featured panel-featured-primary">';
	    html += '<header class="panel-heading">';
	    html += '<div class="panel-actions">';
		html += '<a onclick="optiontoggle('+line+','+option+')" class="panel-action panel-action-toggle" ></a>';
	    html += '<a onclick="removeoption('+line+','+option+','+voption+')" class="panel-action panel-action-dismiss" data-panel-dismiss=""></a>';
	    html += '</div>';
	    html += '<h2 class="panel-title optionheading">Option '+voption+'</h2>';
	    html += '</header>';
		html += '<div id="l'+line+'o'+option+'body" class="panel-body">';
	    html += '<div id="l'+line+'o'+option+'ic">';
		html += '</div>';
	    html += '<div id="l'+line+'o'+option+'ia" class="pull-right">';
	    html += '<button type="button" class="btn btn-primary" onclick="addnewitem('+line+','+option+')" name="button">Add New Item</button>';
		html += '</div>';
	    html += '</div>';
	    html += '</section>';
		
		var descHTML = '<section id="optiondesc'+line+'-'+option+'" style="text-align:center; margin: 0 auto;">';
		descHTML += '<h4 style="color:blue">Option No. '+voption+' SAH Description </h4>';
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
		descHTML += '<tbody id="opdc'+line+'-'+option+'">';
		descHTML += '</tbody>';
		descHTML += '</table>';
		descHTML += '<textarea  style="width: 100%; height: 100%;" id = "l'+line+'o'+option+'desc" cols="90" rows="10"></textarea>';
		descHTML += '<div style="background:#ddd; padding:5px;">';
		descHTML += '<table class="det" width="100%" cellpadding="2">';
		descHTML += '<tbody>';
		descHTML += '<tr>';
		descHTML += '<td>';
		descHTML += 'Stock status';
		descHTML += '</td>';
		descHTML += '<td>';
		descHTML += '<input class="descinfo stockstatus" name="stockstatus" value="'+stockstatus+'">';
		descHTML += '</td>';
		descHTML += '<td>';
		descHTML += '<b>Quantity: </b>';
		descHTML += '</td>';
		descHTML += '<td>';
		descHTML += '<input type="number" class="descinfo quantity" name="quantity" value="'+quantity+'">';
		descHTML += '</td>';
		descHTML += '<td>';
		descHTML += '<b>Price:</b>';
		descHTML += '</td>';
		descHTML += '<td class="pricecont">';
		descHTML += '';
		descHTML += '</td>';
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
		descHTML += '<div id="optionline'+line+'desccontainer" style="text-align:center; margin: 0 auto;"></div>';
		descHTML += '</section>';

		$('#optionline'+line+'desccontainer').append(descHTML);
		
		inittextboxio(line,option,description);
		
	    $('#l'+line+'oc').append(html);

	}

    function addnewitem(line, option){

    	selectedLine = line;
    	selectedOption = option;

    	NProgress.start();

    	var lt = $("#l"+line+"").find("header").find("h2").html();
    	lineno = lt.split(" ")[1];

    	$("#slnum").html(lineno);

    	var ot = $("#l"+line+"o"+option+"").find("header").find("h2").html();
    	optionno = ot.split(" ")[1];
    	
    	$("#sonum").html(optionno);

    	$("#l"+line+"o"+option+"ic > section").each(function(){
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

        $("#searchoverlay").css('display','block');

        NProgress.done();
    
    }

    function additemCallback(indx,line,option,stkc,bname,description,quantity,sprice,uprice,tot,qoh,dis,update,modal,part){

		var item = items[line+","+option] += 1;

        var html = '<section id="l'+line+'o'+option+'i'+indx+'" class="panel panel-featured panel-featured-info">';

        html+='<header class="panel-heading">';
        html+='<div class="panel-actions">';
        html+='<a onclick="removeitem('+line+','+option+','+indx+',\''+description+'\')" class="panel-action panel-action-dismiss" data-panel-dismiss=""></a>';
        html+='</div>';
        html+='<h2 class="panel-title" style="cursor:pointer" onclick="window.open(\'quotationhistory.php?stockid='+stkc+'\',\'Ratting\',\'width=550,height=170,0,status=0,scrollbars=1\');">'+stkc+'</h2>';
        html+='</header>';
        html+='<div class="panel-body">';
        html+='<div class="col-md-6">';
        html+='<div class="col-md-2">';
        html+='Model#:';
        html+='</div>';
        html+='<div class="col-md-9">';
        html+=modal;
        html+='</div>';
        html+='<div class="col-md-2">';
        html+='Part#:';
        html+='</div>';
        html+='<div class="col-md-9">';
        html+=part;
        html+='</div>'
        html+='<div class="col-md-2">';
        html+='Brand:';
        html+='</div>';
        html+='<div class="col-md-9 brandyo">';
        html+=bname;
        html+='</div>';
        html+='<br>';
        html+='<div class="col-md-2">';
        html+='Description:';
        html+='</div>';
        html+='<div class="col-md-9 desc">';
        html+=description;
        html+='</div>';
        html+='<div class="col-md-2">';
        html+='Updated:';
        html+='</div>';
        html+='<div class="col-md-9">';
        html+=update;
        html+='</div>';
        html+='</div>';
        html+='<div id="item'+indx+'" class="col-md-6">';
        html+='<div class="col-md-2">';
        html+='QOH:';
        html+='</div>';
        html+='<div class="col-md-4">';
        html+='<input type="number" value="'+qoh+'" disabled>';
        html+='</div>';
        html+='<div class="col-md-2">';
        html+='Price:';
        html+='</div>';
        html+='<div class="col-md-4">';
        html+='<input class="sprice" type="number" name="" value="'+sprice+'" disabled>';
        html+='</div>';
        html+='<br>';
        html+='<br>';
        html+='<div class="col-md-2">';
        html+='Discount%:';
        html+='</div>';
        html+='<div class="col-md-4">';
        html+='<input class="discount" type="number" value="'+dis+'">';
        html+='</div>';
        html+='<div class="col-md-2">';
        html+='UnitRate:';
        html+='</div>';
        html+='<div class="col-md-4">';
        html+='<input class="uprice" type="number" value="'+uprice+'">';
        html+='</div>';
        html+='<br>';
        html+='<br>';
        html+='<div class="col-md-2">';
        html+='Quantity:';
        html+='</div>';
        html+='<div class="col-md-4">';
        html+='<input class="quantity" type="number" value="'+quantity+'">';
        html+='</div>';
        html+='<div class="col-md-2">';
        html+='Total:';
        html+='</div>';
        html+='<div class="col-md-4">';
        html+='<input class="total" type="number" value="'+tot+'" disabled>';
        html+='</div>';
        html+='</div>';
        html+='</div>';
        html+='</section>';

        var descItemHTML = "<tr id='opdcd"+indx+"'>";
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
        descItemHTML += "<td>";
        descItemHTML += qoh;
        descItemHTML += "</td>";
        descItemHTML += "<td class='quantity'>";
        descItemHTML += quantity;
        descItemHTML += "</td>";
        descItemHTML += "<td class='uprice'>";
        descItemHTML += uprice;
        descItemHTML += "</td>";
        descItemHTML += "<td class='discount'>";
        descItemHTML += Math.round(dis)+'%';
        descItemHTML += "</td>";
        descItemHTML += "</tr>";

        $('#opdc'+line+'-'+option+'').append(descItemHTML);

        $('#l'+line+'o'+option+'ic').append(html);

    }

	function linetoggle(line){
	
		$('#l'+line+'body').toggleClass('toggleviewnone');
	
	}

	function optiontoggle(line,option){

		$('#l'+line+'o'+option+'body').toggleClass('toggleviewnone');
	
	}

	function removeitem(line, option, index, description){

		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();

		swal({
		  title: "Are you sure?",
		  text: (description)+" : will be removed from the option!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: "Yes, delete it!",
		  showLoaderOnConfirm: true,
		  closeOnConfirm: true
		},
		function(){

			NProgress.start();

		  $.ajax({
		  	type: 'POST',
		    url: rootpath+"/quotation/api/deleteItem.php",
		    data: {itemIndex: index},
		    dataType: "json",
		    success: function(response) { 

		    	if(response.status == "success"){

		    		$('#l'+line+'o'+option+'i'+response.data.index).remove();

			    	new PNotify({
						title: 'Success',
						text: "Item ('"+description+"') removed from option!",
						addclass: 'notification-success stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'success'
					});

					items[line+","+option] -= 1;

		    		$("#opdcd"+response.data.index+"").remove();

		    		recalculateoptionboxprice(line,option);

		    	}else{
		    		
		    		new PNotify({
						title: 'Error',
						text: "Item ('"+description+"') delete failed!",
						addclass: 'notification-error stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'error'
					});

		    	}

				NProgress.done();

		    },
		    error: function(){

		    	new PNotify({
					title: 'Error',
					text: "Item ('"+description+"') delete failed!",
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

	function removeoption(line, option, voption){

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
		function(){

			NProgress.start();

		  $.ajax({
		  	type: 'POST',
		    url: rootpath+"/quotation/api/deleteOption.php",
		    data: {line: line, option: option},
		    dataType: "json",
		    success: function(response) { 

		    	if(response.status == "success"){

		    		removeoptionCallback(response.data.line,response.data.option);
		    		new PNotify({
						title: 'Success',
						text: "Option '"+voption+"' deleted successfully!",
						addclass: 'notification-success stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'success'
					});

		    	}else{

		    		new PNotify({
						title: 'Error',
						text: "Option '"+voption+"' delete failed!",
						addclass: 'notification-error stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'error'
					});

		    	}

		    	NProgress.done();

		    },
		    error: function(){

		    	new PNotify({
					title: 'Error',
					text: "Option '"+voption+"' delete failed!",
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

	function removeoptionCallback(line, option){
		$('#l'+line+'o'+option).remove();

		var count = 1;
		$('#l'+line+'oc > section').each(function(){
			$(this).find('.optionheading').html("Option "+(count++));
		});
		
		$('#optiondesc'+line+"-"+option).remove();
		
		var count = 1;
		$('#optionline'+line+'desccontainer > section').each(function(){
			$(this).find('h4').html("Option No. "+(count++)+" SAH Description");
		});

		voptions[line] -= 1;
	
	}

	function removeline(line, vline){

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
		function(){

			NProgress.start();

		  $.ajax({
		  	type: 'POST',
		    url: rootpath+"/quotation/api/deleteLine.php",
		    data: {line: line},
		    dataType: "json",
		    success: function(response) { 

		    	if(response.status == "success"){

		    		removelineCallback(response.data.line);
			    	new PNotify({
						title: 'Success',
						text: "Line '"+vline+"' deleted successfully!",
						addclass: 'notification-success stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'success'
					});

		    	}else{
		    		
		    		new PNotify({
						title: 'Error',
						text: "Line '"+vline+"' delete failed!",
						addclass: 'notification-error stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'error'
					});

		    	}

		    	NProgress.done();
		    },
		    error: function(){
		    	new PNotify({
					title: 'Error',
					text: "Line '"+vline+"' delete failed!",
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

	function removelineCallback(line){		
		$('#l'+line).remove();

		var count = 1;
		$('#linescontainer > section').each(function(){
			$(this).find('.lineheading').html("Line "+(count++));
		});
		
		$('#linedesc'+line).remove();
		
		var count = 1;
		$('#linesdescriptioncontainer > section').each(function(){
			$(this).find('h3').html("Line No. "+(count++)+" Client Requirements");
		});

		vlineno -= 1;

	}
	
	function inittextboxio(line, optionno, content){
		
		var editor =  textboxio.replace("#l"+line+"o"+optionno+"desc", {
			    
			codeview : {
				enabled: false,
				showButton: false
			},
			ui: {
				toolbar: {
					items: ["undo","tools"]
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

			$('.ephox-polish-banner-close').css('display','none');

			de['editor'] = editor;
			de['msg']	 = msg;
			de['line']	 = line;
			de['option'] = optionno;

			if(!(line+"-"+optionno in dirtyEditors))
				dirtyEditors[line+"-"+optionno] = [];
					
			dirtyEditors[line+"-"+optionno].push(de);

		});
		
	}


	$(document.body).on('keyup','input.uprice',function(){

		var itemid 		= $(this).parent().parent().attr('id');
		var discount 	= $(this).parent().parent().find("div > input.discount").val(); 
		var uprice 		= $(this).parent().parent().find("div > input.uprice").val(); 
		var sprice 		= $(this).parent().parent().find("div > input.sprice").val(); 
		var quantity 	= $(this).parent().parent().find("div > input.quantity").val(); 

		if(uprice !== uprice || uprice <= 0){
			$(this).parent().parent().find("div > input.uprice").css("border","red 2px dotted");
		}else{
			$(this).parent().parent().find("div > input.uprice").css("border","");
		}

		var dis = (1- Math.round((uprice / sprice)*10000000)/10000000)*100;

		if(dis !== dis){
			$(this).parent().parent().find("div > input.discount").css("border","red 2px dotted");
		}else{
			$(this).parent().parent().find("div > input.discount").css("border","");
		}

		$(this).parent().parent().find("div > input.discount").val(dis);

		var tot = Math.round(uprice * quantity*100)/100;

		if(tot !== tot){
			$(this).parent().parent().find("div > input.total").css("border","red 2px dotted");
		}else{
			$(this).parent().parent().find("div > input.total").css("border","");
		}

		$(this).parent().parent().find("div > input.total").val(tot);

	});

	$(document.body).on('change','input.uprice',function(){

		var reff = $(this);

		recalculateoptionprice(reff);
	    
		var discount 	= $(this).parent().parent().find("div > input.discount").val(); 
		var uprice 		= $(this).parent().parent().find("div > input.uprice").val(); 
		var sprice 		= $(this).parent().parent().find("div > input.sprice").val(); 
		var quantity 	= $(this).parent().parent().find("div > input.quantity").val(); 

		if(uprice !== uprice || uprice <= 0){
			$(this).parent().parent().find("div > input.uprice").css("border","red 2px dotted");
		}else{

			$(this).parent().parent().find("div > input.uprice").css("border","2px orange solid");
			$(this).parent().parent().find("div > input.discount").css("border","2px orange solid");
		
			var dis = (1- Math.round((uprice / sprice)*10000000)/10000000)*100;

			$(this).parent().parent().find("div > input.uprice").prop("disabled", true);
			$(this).parent().parent().find("div > input.discount").prop("disabled", true);
			updatePrice($(this).parent().parent().attr("id"),uprice,dis);

			if(dis !== dis){
				$(this).parent().parent().find("div > input.discount").css("border","red 2px dotted");
			}

			$(this).parent().parent().find("div > input.discount").val(dis);

			var tot = Math.round(uprice * quantity*100)/100;

			if(tot !== tot || tot <= 0){
				$(this).parent().parent().find("div > input.total").css("border","red 2px dotted");
			}else{
				$(this).parent().parent().find("div > input.total").css("border","");
			}

			$(this).parent().parent().find("div > input.total").val(tot);

		}

	});

	$(document.body).on('keyup','input.quantity',function(){

		var uprice 		= $(this).parent().parent().find("div > input.uprice").val(); 
		var quantity 	= $(this).parent().parent().find("div > input.quantity").val(); 

		var tot = Math.round(uprice * quantity*100)/100;

		if(tot !== tot){
			$(this).parent().parent().find("div > input.total").css("border","red 2px dotted");
		}else{
			$(this).parent().parent().find("div > input.total").css("border","");
		}

		$(this).parent().parent().find("div > input.total").val(tot);

		if(quantity !== quantity || quantity <= 0){
			$(this).parent().parent().find("div > input.quantity").css("border","red 2px dotted");
		}else{
			$(this).parent().parent().find("div > input.quantity").css("border","");
		}

	});

	$(document.body).on('change','input.quantity',function(){

		var reff = $(this);

		if(reff.parent().parent().parent().parent().attr("class") == "det")
			return;

		recalculateoptionprice(reff);

	   	var uprice 		= $(this).parent().parent().find("div > input.uprice").val(); 
		var quantity 	= $(this).parent().parent().find("div > input.quantity").val(); 

		var tot = Math.round(uprice * quantity*100)/100;

		if(tot !== tot){
			$(this).parent().parent().find("div > input.total").css("border","red 2px dotted");
		}else{
			$(this).parent().parent().find("div > input.total").css("border","");
		}

		$(this).parent().parent().find("div > input.total").val(tot);

		if(quantity !== quantity || quantity <= 0){
			$(this).parent().parent().find("div > input.quantity").css("border","red 2px dotted");
		}else{

			$(this).parent().parent().find("div > input.quantity").css("border","2px orange solid");
			$(this).parent().parent().find("div > input.quantity").prop("disabled", true);
			updateQuantity($(this).parent().parent().attr("id"),quantity);

		}

	});

	$(document.body).on('keyup','input.discount',function(){

		var discount 	= $(this).parent().parent().find("div > input.discount").val(); 
		var uprice 		= $(this).parent().parent().find("div > input.uprice").val(); 
		var sprice 		= $(this).parent().parent().find("div > input.sprice").val(); 
		var quantity 	= $(this).parent().parent().find("div > input.quantity").val(); 

		if(discount !== discount){
			$(this).parent().parent().find("div > input.discount").css("border","red 2px dotted");
		}else{
			$(this).parent().parent().find("div > input.discount").css("border","");
		}

		var uprice = (sprice)-((sprice/100)*discount);

		if(uprice !== uprice){
			$(this).parent().parent().find("div > input.uprice").css("border","red 2px dotted");
		}else{
			$(this).parent().parent().find("div > input.uprice").css("border","");
		}

		$(this).parent().parent().find("div > input.uprice").val(uprice);

		var tot = Math.round(uprice * quantity*100)/100;

		if(tot !== tot || tot <= 0){
			$(this).parent().parent().find("div > input.total").css("border","red 2px dotted");
		}else{
			$(this).parent().parent().find("div > input.total").css("border","");
		}

		$(this).parent().parent().find("div > input.total").val(tot);

	});

	$(document.body).on('change','input.discount',function(){

		var reff = $(this);
	    
		var discount 	= $(this).parent().parent().find("div > input.discount").val(); 
		var uprice 		= $(this).parent().parent().find("div > input.uprice").val(); 
		var sprice 		= $(this).parent().parent().find("div > input.sprice").val(); 
		var quantity 	= $(this).parent().parent().find("div > input.quantity").val(); 

		if(discount !== discount || discount >= 100){
			$(this).css("border","red 2px dotted");
		}else{
			
			$(this).css("border","2px orange solid");
			$(this).parent().parent().find("div > input.uprice").css("border","2px orange solid");

			recalculateoptionprice(reff);
			
			var uprice = (sprice)-((sprice/100)*discount);

			$(this).parent().parent().find("div > input.uprice").prop("disabled", true);
			$(this).parent().parent().find("div > input.discount").prop("disabled", true);
			updatePrice($(this).parent().parent().attr("id"),uprice,discount);

			if(uprice !== uprice){
				$(this).parent().parent().find("div > input.uprice").css("border","red 2px dotted");
			}

			$(this).parent().parent().find("div > input.uprice").val(uprice);

			var tot = Math.round(uprice * quantity*100)/100;

			if(tot !== tot || tot <= 0){
				$(this).parent().parent().find("div > input.total").css("border","red 2px dotted");
			}else{
				$(this).parent().parent().find("div > input.total").css("border","");
			}

			$(this).parent().parent().find("div > input.total").val(tot);

		}

	});

	function updateQuantity(item,quantity){
		
		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();

		$.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/updateItem.php",
		    data: {orderno: order, salescaseref: salesref, name: "quantity",value:quantity,item:item},
		    dataType: "json",
		    success: function(response) { 
		    	var status = response.status;
		    	
		    	if(status == "success"){

		    		$("#"+item+"").find("input.quantity").css("border","2px green solid");
		    		$("#"+item+"").find("input.quantity").val(response.data.value);
		    		var id = item.split("item")[1];
		    		$("#opdcd"+id+"").find(".quantity").html(response.data.value);
		    		$("#"+item+"").find("input.quantity").prop("disabled", false);

		    	}else{
		    		
		    		$("#"+item+"").find("input.quantity").css("border","2px red solid");
		    		$("#"+item+"").find("input.quantity").prop("disabled", false);

		    	}
		    },
		    error: function(){

		    	$("#"+item+"").find("input.quantity").css("border","2px red solid");
	    		$("#"+item+"").find("input.quantity").prop("disabled", false);
		    
		    }

		});

	}

	function updatePrice(item,uprice,discount){

		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();

		$.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/updateItem.php",
		    data: {orderno: order, salescaseref: salesref, name: "uprice",value:uprice,item:item,discount:discount},
		    dataType: "json",
		    success: function(response) { 
		    	var status = response.status;
		    	
		    	if(status == "success"){

		    		$("#"+item+"").find("input.uprice").css("border","2px green solid");
		    		$("#"+item+"").find("input.uprice").val(response.data.value);
		    		$("#"+item+"").find("input.uprice").prop("disabled", false);

		    		$("#"+item+"").find("input.discount").css("border","2px green solid");
		    		$("#"+item+"").find("input.discount").val((response.data.discount)*100);
		    		$("#"+item+"").find("input.discount").prop("disabled", false);

		    		var id = item.split("item")[1];
		    		$("#opdcd"+id+"").find(".uprice").html(response.data.value);
		    		$("#opdcd"+id+"").find(".discount").html(((response.data.discount)*100)+"%");

		    	}else{
		    		
		    		$("#"+item+"").find("input.uprice").css("border","2px red solid");
		    		$("#"+item+"").find("input.uprice").prop("disabled", false);

		    		$("#"+item+"").find("input.discount").css("border","2px red solid");
		    		$("#"+item+"").find("input.discount").prop("disabled", false);

		    	}

		    },
		    error: function(){

		    	$("#"+item+"").find("input.uprice").css("border","2px red solid");
	    		$("#"+item+"").find("input.uprice").prop("disabled", false);

	    		$("#"+item+"").find("input.discount").css("border","2px red solid");
	    		$("#"+item+"").find("input.discount").prop("disabled", false);
		    
		    }

		});

	}

	$(document).ready(function(){
		table = $('#searchresults').DataTable({
			"columns": [
	            { "data": "stockid" },
	            { "data": "mnfcode" },
	            { "data": "mnfpno" },
	            { "data": "qoh" },
	            { "data": "mname" },
	            { "data": "description" },
	            { "data": "action" },
        	]
		});

	});

	$("#bss").click(function(e){
		e.preventDefault();

    	NProgress.start();


		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();

		var code = $("#scode").val();
		var cat  = $("#scat").val();

		$.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/searchtemp.php",
		    data: {StockCode: code, StockCat: cat},
		    dataType: "json",
		    success: function(response) { 

		    	var status = response.status;   	

		    	table.clear().draw();	
		    	table.rows.add(response.data).draw();

		    	NProgress.done();

		    },
		    error: function(){

		    	NProgress.done();

		    }

		});

	});

	$("#bcr").click(function(e){
		e.preventDefault();

    	NProgress.start();

		table.clear().draw();

    	NProgress.done();

	});

	function insertitem(itemindex, brand){

		NProgress.start();

		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();

		var line = selectedLine;
		var option = selectedOption;

		$('button').prop('disabled',true);

		$.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/addNewItem.php",
		    data: {orderno: order, salescaseref: salesref, line:line,option:option,item_id:itemindex},
		    dataType: "json",
		    success: function(response) { 

		    	var status = response.status;   	

		    	if(status == "success"){

		    		var d = response.data;
					
					additemCallback(d.id,d.line,d.option,d.title,brand,d.desc,d.quantity,d.price,d.price,d.total,d.qoh,d.discount,d.update,d.model,d.part);

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
						text: ''+d.desc+'\n Added successfully .',
						addclass: 'notification-success stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'success'
					});
		    	
		    	}else{

		    		new PNotify({
						title: 'Warning',
						text: response.message,
						addclass: 'notification-warning stack-topleft',
						width: "300px",
						delay: 1000,
						type: 'warning'
					});

		    	}

		    	$('button').prop('disabled',false);
		    	NProgress.done();

		    },
		    error: function(){

		    	new PNotify({
					title: 'Error',
					text: 'Item could not be added.',
					addclass: 'notification-error stack-topleft',
					width: "300px",
					delay: 1000,
					type: 'error'
				});

		    	$('button').prop('disabled',false);
		    	NProgress.done();
		    	
		    }

		});

	}

	$(document.body).on('keyup','.order-detailss',function(){
		$(this).css("border","");
	});

	$(document.body).on('change','.order-detailss',function(){

	    NProgress.start();

	    var ref = $(this);

		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();
	    
	    var name = $(this).attr("name");
	    var value = $(this).val();

	    if(name == "services"){
	    	value = ($(this).is(":checked")) ? 1 : 0;
	    }

	    $(this).prop("disabled",true);

		ref.css("border","2px orange solid");					

	    $.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/updateOrderDetails.php",
		    data: {orderno: order, salescaseref: salesref, name:name, value:value},
		    dataType: "json",
		    success: function(response) { 

		    	var status = response.status;   	

		    	if(status == "success"){

					ref.css("border","2px green solid");					
		    	
		    	}else{

					ref.css("border","2px red solid");					

		    	}

		    	ref.prop("disabled",false);
		    	
		    	NProgress.done();

		    },
		    error: function(){

		    	ref.prop("disabled",false);
				ref.css("border","2px red solid");					
		    	NProgress.done();
		    	
		    }

		});


	});

	$(document.body).on('change','.descinfo',function(){

	    NProgress.start();

	    var ref = $(this);

		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();
	    
	    var name = $(this).attr("name");
	    var value = $(this).val();

	    var option = $(this).parent().parent().parent().parent().parent().parent().attr("id").split("-")[1];

	    if(name == "services"){
	    	value = ($(this).is(":checked")) ? 1 : 0;
	    }

	    $(this).prop("disabled",true);

		ref.css("border","2px orange solid");					

	    $.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/updateOption.php",
		    data: {orderno: order, salescaseref: salesref, name:name, value:value, option:option},
		    dataType: "json",
		    success: function(response) { 

		    	var status = response.status;   	

		    	if(status == "success"){

					ref.css("border","2px green solid");
					updateoptionquantity(ref,name,value);					
		    	
		    	}else{

					ref.css("border","2px red solid");					

		    	}

		    	ref.prop("disabled",false);
		    	
		    	NProgress.done();

		    },
		    error: function(){

		    	ref.prop("disabled",false);
				ref.css("border","2px red solid");					
		    	NProgress.done();
		    	
		    }

		});

	});

	$("#erbtn").click(function(e){
		
		e.preventDefault();

		NProgress.start();

		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();

		$("#epreviewcontainer").html("");

		$.ajax({
			type: 'GET',
		    url: rootpath+"/PDFQuotationIP.php?QuotationNo="+order+"&root="+rootpath,
		    dataType: "json",
		    success: function(response) { 

	    		var ihtml = "<iframe ";
				ihtml += 'id="previewframe" ';
				ihtml += 'src = "ViewerJS/#../tempPDF/'+order+'ext.pdf" ';
				ihtml += "width='100%' height='1024'";
				ihtml += ' allowfullscreen webkitallowfullscreen></iframe>';
				
				$("#epreviewcontainer").html(ihtml);	
	      	
		    	NProgress.done();

		    },
		    error: function(){
				
		    	NProgress.done();
		    	
		    }

		});
		
	});

	$("#irbtn").click(function(e){
		e.preventDefault();

		NProgress.start();

		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();

		$("#ipreviewcontainer").html("");

		$.ajax({
			type: 'GET',
		    url: rootpath+"/PDFQuotationIPint.php?QuotationNo="+order+"&root="+rootpath,
		    dataType: "json",
		    success: function(response) { 

		    	console.log(response);

		    	if(response.status == "success"){
		    		var ihtml = "<iframe ";
					ihtml += 'id="previewframe" ';
					ihtml += 'src = "ViewerJS/#../tempPDF/'+order+'int.pdf" ';
					ihtml += "width='100%' height='1024'";
					ihtml += ' allowfullscreen webkitallowfullscreen></iframe>';
					
					$("#ipreviewcontainer").html(ihtml);	
		    	}
		    	
		    	NProgress.done();

		    },
		    error: function(){
				
		    	NProgress.done();
		    	
		    }

		});

	});

	$("#checkforwarnings").click(function(e){

		NProgress.start();

		var ref = $(this);

		ref.prop("disabled",true);

		var order = $('#orderno').val();
		var rootpath = $('#rootpath').val();
		var salesref = $('#salesref').val();

		$.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/checkforwarnings.php",
		    data:{salescaseref:salesref,orderno:order},
		    dataType: "json",
		    success: function(r) { 

		    	$("#warningscontainer").html("");

		    	if(r.elines == 0 && r.eoptions == 0 && r.itemsuo == 0 && r.items == 0 && r.options == 0 && r.lines != 0 && r.itemswq > 0){

		    		savequotation(ref,rootpath,salesref,order,r.formid);

		    	}else{

		    		var html = '<div class="alert alert-danger">';

		    		if(r.status == "error"){
		    			
		    			window.location = ""+rootpath+"/salescase.php/?salescaseref="+salesref+"";
		    			return;
		    		}

		    		if(r.lines == 0){
			    		
			    		html += '<strong>';
				    	html += 'No lines added in quotation.';
			    		html += '</strong>';
			    		html += '</br>';

			    	}

			    	if(r.elines != 0){
			    		
			    		html += '<strong>';
				    	html += r.elines;
				    	html += ' Empty Lines Found.';
			    		html += '</strong>';
			    		html += '</br>';

			    	}

			    	if(r.eoptions != 0){
			    		
			    		html += '<strong>';
				    	html += r.eoptions;
				    	html += ' Empty Options Found.';
			    		html += '</strong>';
			    		html += '</br>';

			    	}

			    	if(r.options != 0){
			    		
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

			    	if(r.items != 0){
			    		
			    		html += '<strong>';
				    	html += r.items;
				    	html += ' Items Found with 0 quantity.';
			    		html += '</strong>';
			    		html += '</br>';

			    	}

			    	if(r.itemswq == 0){
			    		
			    		html += '<strong>';
				    	html += 'Atleast 1 item needs to be added with quantity > 0';
			    		html += '</strong>';
			    		html += '</br>';

			    	}

			    	html += '</div>';

			    	if(r.lines != 0 && r.itemswq > 0){
			    		//$("#proceedanyway").css("display","block");
			    	}
		    		$("#warningscontainer").html(html);
		    		NProgress.done();
					ref.prop("disabled",false);

		    	}

				//<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

		    },
		    error: function(){
				
		    	$("#warningscontainer").html("");

		    	var html = '<div class="alert alert-danger">';
		    	html += "<strong>";
		    	html += "Request Could Not Be Completed for some reason.";
		    	html += "</strong>";
		    	html += "</div>";

		    	$("#warningscontainer").html(html);

				ref.prop("disabled",false);
		    	NProgress.done();
		    	
		    }

		});

	});

	function savequotation(ref,rootpath,salesref,orderno,formid){


		
		$.ajax({
			type: 'POST',
		    url: rootpath+"/quotation/api/saveFinalQuotation.php",
		    data:{salescaseref:salesref,orderno:orderno,FormID:formid},
		    dataType: "json",
		    success: function(r) { 

				ref.prop("disabled",false);
		    	NProgress.done();
		    	window.location = ""+rootpath+"/salescase.php/?salescaseref="+salesref+"";

		    },
		    error: function(){
				
		    	$("#warningscontainer").html("");

		    	var html = '<div class="alert alert-danger">';
		    	html += "<strong>";
		    	html += "Quotation Save Failed.";
		    	html += "</strong>";
		    	html += "</div>";

		    	$("#warningscontainer").html(html);

				ref.prop("disabled",false);
		    	NProgress.done();
		    	
		    }

		});

	}

	function updateoptionquantity(ref, name, value){

		if(name != "quantity"){
			return;
		}

		ref.parent().parent().find(".tpricecont").html(Math.round(Number(ref.parent().parent().find(".pricecont").html())*value*100)/100);

	}

	function recalculateoptionprice(ref){

		var parent = ref.parent().parent().parent().parent().parent();
		var parrentid = parent.attr("id");
		var total = 0;

		var line = parrentid.split("o")[0].split("l")[1];
		var option = parrentid.split("o")[1].split("ic")[0];
		

		parent.find("section").each(function(){

			var item = $(this);

			total += Number(item.find(".total").val());
		
		});

		$("#optiondesc"+line+"-"+option+"").find(".pricecont").html(total);
		
		var quantity = $("#optiondesc"+line+"-"+option+"").find(".det").find(".quantity").val();
		var subtotal = quantity * total;

		subtotal = Math.round(subtotal*100)/100;
		
		$("#optiondesc"+line+"-"+option+"").find(".tpricecont").html(subtotal);

	}

	function recalculateoptionboxprice(line,option){

		var parent = $("#l"+line+"o"+option+"ic");
		var parrentid = parent.attr("id");
		var total = 0;

		var line = parrentid.split("o")[0].split("l")[1];
		var option = parrentid.split("o")[1].split("ic")[0];
		

		parent.find("section").each(function(){

			var item = $(this);

			total += Number(item.find(".total").val());
		
		});

		$("#optiondesc"+line+"-"+option+"").find(".pricecont").html(total);
		
		var quantity = $("#optiondesc"+line+"-"+option+"").find(".det").find(".quantity").val();
		var subtotal = quantity * total;

		subtotal = Math.round(subtotal*100)/100;
		
		$("#optiondesc"+line+"-"+option+"").find(".tpricecont").html(subtotal);

	}

