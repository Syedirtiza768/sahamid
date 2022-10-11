var datatable;
var datatableInit = function() {

	datatable = $('#datatable').DataTable({
		language: {
	        search: "_INPUT_",
	        searchPlaceholder: "Search..."
	    },
	    "columnDefs": [
		    { 
		    	className: "fit center", 
		    	"render": function ( data, type, row ) {
        			return data;
    			},
		    	"targets": [ 0 ] 
		    },
		    { 
		    	className: "fit no-wrap", 
		    	"render": function ( data, type, row ) {
		    		let html = "<div class='tooltip'>"+data;
		    		html += "<span class='tooltiptext'>";
		    		html += "<table style='width:100%' class='table'>";
		    		html += "<tr>";
		    		html += "<td>Enquiry Date</td>";
		    		html += "<td>"+row['ed']+"</td>";
		    		html += "</tr>";
		    		html += "<tr>";
		    		html += "<td>Salesman</td>";
		    		html += "<td>"+row['salesman']+"</td>";
		    		html += "</tr>";
		    		html += "<tr>";
		    		html += "<td>Enquiry Value</td>";
		    		html += "<td>"+row['ev']+" <sub>PKR</sub></td>";
		    		html += "</tr>";
		    		html += "<tr>";
		    		html += "<td>Quotation</td>";
		    		html += "<td><a target='_blank' href='../PDFQuotation.php/?QuotationNo="+row['hq']+"'>"+((row['hq'] == null) ? "":row['hq'])+"</a></td>";
		    		html += "</tr>";
		    		html += "<tr>";
		    		html += "<td>Quotation Value</td>";
		    		html += "<td>"+row['qv']+" <sub>PKR</sub></td>";
		    		html += "</tr>";
					html += "<tr>";
		    		html += "<td>PO No</td>";
		    		html += "<td>"+row['pon']+"</td>";
		    		html += "</tr>";
		    		html += "<tr>";
		    		html += "<td>PO Date</td>";
		    		html += "<td>"+row['pd']+"</td>";
		    		html += "</tr>";
		    		html += "<tr>";
		    		html += "<td>DC</td>";
		    		html += "<td>"+row['dc']+"</td>";
		    		html += "</tr>";
					html += "<tr>";
		    		html += "<td>DBA</td>";
		    		html += "<td>"+row['dba']+"</td>";
		    		html += "</tr>";
		    		html += "</table>";
		    		html += "</span>";
		    		html += "</div>";
        			return html;
    			}, 
		    	"targets": [ 1 ] 
		    },
		    { 
		    	className: "fit center", 
		    	"render": function ( data, type, row ) {
		    		return row['description'];
    			}, 
    			"targets": [ 2 ] 
    		},
		    { 
		    	className: "fit no-wrap", 
		    	"render": function ( data, type, row ) {
		    		return row['client'];
    			}, 
		    	"targets": [ 3 ] 
		    },
		    { 
		    	className: "fit center", 
		    	"render": function ( data, type, row ) {
		    		return "<a target='_blank' href='../PDFQuotation.php/?QuotationNo="+row['hq']+"'>"+((row['hq'] == null) ? "":row['hq'])+"</a>";
    			},
		    	"targets": [ 4 ] 
		    },
			{ className: "fit center", "targets": [ 5 ] },
			{ className: "fit center", "targets": [ 6 ] },
			{ className: "fit center", "targets": [ 7 ] },
		    
		    { 
		    	className: "fit center",
		    	"render": function ( data, type, row ) {
		    		let html = "<div class='abcdefgh'><a href='salescaseview.php?salescaseref="+row['salescaseref'];
		    		html += "' target='_blank' class='btn btn-info action'>view</a>";
		    		
					if(row['wl'] == false){
		    			html += "<div class='wlcont'><button data-salescaseref='"+row['salescaseref'];
		    			html += "' class='btn btn-success salescasewatchlist action'>+Watchlist</button></div>";
		    		}else{
		    			html += "<div class='wlcont'><button data-salescaseref='"+row['salescaseref'];
		    			html += "' class='btn btn-danger salescasewatchlist action'>-Watchlist</button></div>";
		    		}
					
					html += "</div>";
					
        			return html;
    			}, 
		    	"targets": [ 8 ],
		    }

		],
		columns:[
			{ data: "sno"},
            { data: "salescaseref"},
            { data: "description"},
            { data: "client"},
            { data: "hq"},
			{ data: "pono"},
			{ data: "podate"},
			{ data: "qv"},
            { data: "action"}
		],
		"pageLength": 11
		/* <?php echo $_SESSION['DefaultDisplayRecordsMax']; ?>*/
	});

	$('#datatable_length')
		.find("label")
		.html(`<h3 style='margin:0; padding:0; font-variant: petite-caps; dispay:inline-block;'>
					Select Salescase
				</h3>
				<div class="custom_filters">
					&nbsp;&nbsp;&nbsp; DBA:
					<input type="text" class="input" placeholder="DBA" style="border:1px solid #ccc; border-radius:7px;">
					&nbsp;&nbsp;&nbsp; DC#:
					<input type="text" class="input" placeholder="DC#" style="border:1px solid #ccc; border-radius:7px;">
					&nbsp;&nbsp;&nbsp; Quotation#:
					<input type="text" class="input" placeholder="Quotation#" style="border:1px solid #ccc; border-radius:7px;">
				</div>`);

	$('#datatable tfoot th').each( function (i) {
        var title = $('#datatable thead th').eq( $(this).index() ).text();
        if($(this).html() != "SNo" && 
        	$(this).html() != "Action" && 
        	$(this).html() != "Start Date" && 
			$(this).html() != "Enquiry" && 
			$(this).html() != "PO No" && 
			$(this).html() != "PO Date" && 
        	$(this).html() != "Details"){
        	$(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" />' );
        }
    });

	datatable.columns().every( function () {
        var that = this;
 
        $('input', this.footer()).on('keyup change', function (){
            if(that.search() !== this.value){
                that.search(this.value).draw();
            }
        });
    });

};

$(document).ready(function(){
	datatableInit();
	$("tbody tr td").html("Apply Filters And Search");
});

$(document.body).on("click",".salescasewatchlist",function(){

	let ref = $(this);
	let salescaseref = ref.attr("data-salescaseref");

	ref.prop("disabled",true);
	ref.html("Processing");

	$.get("api/updateWatchlistStatus.php?salescaseref="+salescaseref,function(res, status){
	
		ref.prop("disabled",false);

		res = JSON.parse(res);
		if(res.status == "error"){
			swal("Error",res.message,"error");
		}else{
			if(res.action == "INSERT"){
				ref.parent().html(watchlistRemove(salescaseref));
			}else{
				ref.parent().html(watchlistAdd(salescaseref));
			}
		}
	});

});

function watchlistRemove(salescaseref){
	let html = "<button data-salescaseref='"+salescaseref;
	html += "' class='btn btn-danger salescasewatchlist action'>-Watchlist</button>";
	return html;
}

function watchlistAdd(salescaseref){
	let html = "<button data-salescaseref='"+salescaseref;
	html += "' class='btn btn-success salescasewatchlist action'>+Watchlist</button>";
	return html;
}

$(".filtercase").on("click",function(e){
	e.preventDefault();

	let filter = "filters=yes";

	let salesperson = $(".salesperson").val().trim();

	if(salesperson != ""){
		filter += "&salesperson="+salesperson;
	}

	let client = $(".client").val().trim();

	if(client != ""){
		filter += "&client="+client;
	}

	let salescaseref = $(".salescaseref").val().trim();

	if(salescaseref != ""){
		filter += "&salescaseref="+salescaseref;
	}

	let location = $(".location").val();

	if(location != ""){
		filter += "&location="+location;
	}

	let priority = $(".priority").val();

	if(priority != ""){
		filter += "&priority="+priority;
	}

	let director = $(".director").val();

	if(director != ""){
		filter += "&director="+director;
	}

	let dba = $(".dba").val();

	if(dba != ""){
		filter += "&dba="+dba;
	}
	
	let dc = $(".deliverychalan").val();

	if(dc != ""){
		filter += "&dc="+dc;
	}
	
	let po = $(".purchaseorder").val();

	if(po != ""){
		filter += "&po="+po;
	}
	
	let quot = $(".quotation").val();

	if(quot != ""){
		filter += "&quot="+quot;
	}
	
	let from_date = $(".from_date").val();

	if(from_date != ""){
		filter += "&from_date="+from_date;
	}
	
	let to_date = $(".to_date").val();

	if(to_date != ""){
		filter += "&to_date="+to_date;
	}
	
	let for_month = $(".for_month").val();

	if(for_month != ""){
		filter += "&for_month="+for_month;
	}
	
	let for_year = $(".for_year").val();

	if(for_year != ""){
		filter += "&for_year="+for_year;
	}

	datatable.clear().draw();

	$("tbody tr td").html("Searching...");
	$.get("api/selectSalescaseAllApi.php?"+filter, function(res, status){
		datatable.rows.add(JSON.parse(res)).draw(false);
	});

});