<?php 

	$active = "dashboard";

	include_once("config.php");

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>
<link rel="stylesheet" href="../salescase/assets/selectSalescase.css"/>
<div class="content-wrapper">
    <section class="content-header">
      
    </section>

    <section class="content">
	    
		<div class="row">
			
			<div class="col-md-12">
				
				<table class="table table-bordered table-striped mb-none" id="datatable">
					<thead>
						<tr style="background-color: #424242; color: white">
							<th class="fit">SNo</th>
							<th class="fit">Salescaseref</th>
							<th class="fit">Salesman</th>
							<th class="">Client</th>
							<th class="fit no-wrap">Start Date</th>
							<th class="fit no-wrap">Details</th>
							<th class="">Action</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
				
			</div>
		
		</div>

    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>
<script>
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
						html += "<span class='tooltiptext'>"+row['description']+"</span>";
						html += "</div>";
						return html;
					}, 
					"targets": [ 1 ] 
				},
				{ className: "fit center", "targets": [ 2 ] },
				{ className: "fit no-wrap", "targets": [ 3 ] },
				{ className: "fit center", "targets": [ 4 ] },
				{ 
					className: "fit no-wrap center", 
					"render": function ( data, type, row ) {
						let html = "<div class='tooltip'>------";
						html += "<span class='tooltip-left'>";
						html += "<table style='width:100%' class='table'>";
						html += "<tr>";
						html += "<td style='color:black'>Enquiry Date</td>";
						html += "<td style='color:black'>"+row['ed']+"</td>";
						html += "</tr>";
						html += "<tr>";
						html += "<td style='color:black'>Enquiry Value</td>";
						html += "<td style='color:black'>"+row['ev']+" <sub>PKR</sub></td>";
						html += "</tr>";
						html += "<tr>";
						html += "<td style='color:black'>Quotation</td>";
						html += "<td style='color:black'><a style='color:black' target='_blank' href='../PDFQuotation.php/?QuotationNo="+row['hq']+"'>"+((row['hq'] == null) ? "":row['hq'])+"</a></td>";
						html += "</tr>";
						html += "<tr>";
						html += "<td style='color:black'>Quotation Value</td>";
						html += "<td style='color:black'>"+row['qv']+" <sub>PKR</sub></td>";
						html += "</tr>";
						html += "<tr>";
						html += "<td style='color:black'>PO Date</td>";
						html += "<td style='color:black'>"+row['pd']+"</td>";
						html += "</tr>";
						html += "<tr>";
						html += "<td style='color:black'>DC</td>";
						html += "<td style='color:black'>"+row['dc']+"</td>";
						html += "</tr>";
						html += "<tr>";
						html += "<td style='color:black'>DBA</td>";
						html += "<td style='color:black'>"+row['dba']+"</td>";
						html += "</tr>";
						html += "</table>";
						html += "</span>";
						html += "</div>";
						return html;
					}, 
					"targets": [ 5 ] 
				},
				{ 
					className: "fit center",
					"render": function ( data, type, row ) {
						let html = "<a href='../salescase/salescaseview.php?salescaseref="+row['salescaseref'];
						html += "' target='_blank' class='btn btn-info action'>view</a>";
						
						if(row['wl'] == false){
							html += "<div class='wlcont'><button data-salescaseref='"+row['salescaseref'];
							html += "' class='btn btn-success salescasewatchlist action'>+Watchlist</button></div>";
						}else{
							html += "<div class='wlcont'><button data-salescaseref='"+row['salescaseref'];
							html += "' class='btn btn-danger salescasewatchlist action'>-Watchlist</button></div>";
						}
						
						return html;
					}, 
					"targets": [ 6 ],
				}

			],
			columns:[
				{ data: "sno"},
				{ data: "salescaseref"},
				{ data: "salesman"},
				{ data: "client"},
				{ data: "commencement"},
				{ data: "details"},
				{ data: "action"}
			],
			"pageLength": 10
			/* <?php echo $_SESSION['DefaultDisplayRecordsMax']; ?>*/
		});

		$('#datatable_length')
			.find("label")
			.html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Salescases This Month</h3>");


	};

	$(document).ready(function(){
		datatableInit();
		$("tbody tr td").html("Loading...");
		$.get("api/selectSalescaseAllApi.php", function(res, status){
			datatable.rows.add(JSON.parse(res)).draw(false);
		});
	});
	
	$(document.body).on("click",".salescasewatchlist",function(){

		let ref = $(this);
		let salescaseref = ref.attr("data-salescaseref");

		ref.prop("disabled",true);
		ref.html("Processing");

		$.get("../salescase/api/updateWatchlistStatus.php?salescaseref="+salescaseref,function(res, status){
		
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
</script>
<?php
	include_once("includes/foot.php");
?>