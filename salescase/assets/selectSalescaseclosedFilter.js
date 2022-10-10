$(".filtercase").on("click",function(e){
	e.preventDefault();

	let filter = "filters=yes";

	let salesperson = $(".salesperson").val().trim();

	if(salesperson != ""){
		filter += "%26salesperson="+salesperson;
	}

	let client = $(".client").val().trim();

	if(client != ""){
		filter += "%26client="+client;
	}

	let salescaseref = $(".salescaseref").val().trim();

	if(salescaseref != ""){
		filter += "%26salescaseref="+salescaseref;
	}

	let location = $(".location").val();

	if(location != ""){
		filter += "%26location="+location;
	}

	let priority = $(".priority").val();

	if(priority != ""){
		filter += "%26priority="+priority;
	}

	let director = $(".director").val();

	if(director != ""){
		filter += "%26director="+director;
	}

	let dba = $(".dba").val();

	if(dba != ""){
		filter += "%26dba="+dba;
	}
	
	let dc = $(".deliverychalan").val();

	if(dc != ""){
		filter += "%26dc="+dc;
	}
	
	let po = $(".purchaseorder").val();

	if(po != ""){
		filter += "%26po="+po;
	}
	
	let quot = $(".quotation").val();

	if(quot != ""){
		filter += "%26quot="+quot;
	}
	
	let from_date = $(".from_date").val();

	if(from_date != ""){
		filter += "%26from_date="+from_date;
	}
	
	let to_date = $(".to_date").val();

	if(to_date != ""){
		filter += "%26to_date="+to_date;
	}
	
	let for_month = $(".for_month").val();

	if(for_month != ""){
		filter += "%26for_month="+for_month;
	}
	
	let for_year = $(".for_year").val();

	if(for_year != ""){
		filter += "%26for_year="+for_year;
	}

	let url = "selectsalescase.php?url="+filter;

	window.location.href = url;

});