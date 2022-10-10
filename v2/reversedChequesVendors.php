<?php 

	$active = "dashboard";

	include_once("config.php");
//	echo $_SESSION['UsersRealName'];

	if(isset($_GET['json'])){
		
		$SQL = "SELECT `id`, `transno`, `client`, `chequeDate`,
                `revDate`, `UserID`, `chequeno`, `amount`,
                `reason`, `chequefilepath`,
                `chequedepositfilepath` 
                FROM `reversedallocationhistoryvendor`";
		$res = mysqli_query($db, $SQL);
		
		$response = [];
		
		$count = 1;
		while($row = mysqli_fetch_assoc($res)){
			
			$row['index'] = $count;
			//$row['chequeDate'] = date("Y/m/d",strtotime($row['chequeDate']));
            //$row['revDate'] = date("Y/m/d",strtotime($row['revDate']));

            $response[] = $row;
			$count++;
			
		}
		
		echo json_encode($response);
		return;
		
	}
	
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
							<th class="fit">Client</th>
                            <th class="fit">ChequeNo</th>
							<th class="fit">ChequeDate</th>
							<th class="fit">revDate</th>
                            <th class="fit">Reversed By</th>
							<th class="fit">Amount</th>
							<th class="fit">Reason</th>
							<th class="fit">CDR</th>
							<th class="fit no-wrap">Cheque</th>
                            <th class="fit no-wrap">Comments</th>
                            <th class="fit no-wrap"></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
					<tfoot>
                    <tr style="background-color: #424242; color: white">
                        <th class="fit">SNo</th>
                        <th class="fit">Client</th>
                        <th class="fit">ChequeNo</th>
                        <th class="fit">ChequeDate</th>
                        <th class="fit">revDate</th>
                        <th class="fit">Reversed By</th>
                        <th class="fit">Amount</th>
                        <th class="fit">Reason</th>
                        <th class="fit">CDR</th>
                        <th class="fit no-wrap">Cheque</th>
                        <th class="fit no-wrap">Comments</th>
                        <th class="fit no-wrap"></th>
                    </tr>
					</tfoot>
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

						if(data != null && data != ""){
							html = "<a class='btn btn-info' href='"+data+"' target='_blank'>Download</a>";
						}else{
							html = "";
						}
						return html;
					},
					"targets": [8]
				},
				{ 
					className: "fit center", 
					"render": function ( data, type, row ) {
						if(data != null && data != ""){
							html = "<a class='btn btn-primary' href='"+data+"' target='_blank'>Download</a>";
						}else{
							html = "";
						}
						return html;
					},
					"targets": [9]
				},
                {
                    className: "fit center",
                    "render": function ( data, type, row ) {
                        /*  if(data != null && data != ""){
                              html = "<a class='btn btn-primary' href='"+data+"' target='_blank'>Download</a>";
                          }else{
                              html = "";
                          }*/
                        html='<textarea class="comment" data-revid="'+row['id']+'"></textarea>' ;
                        return html;
                    },
                    "targets": [10]
                },
                {
                    className: "fit center",
                    "render": function ( data, type, row ) {
                        /*  if(data != null && data != ""){
                              html = "<a class='btn btn-primary' href='"+data+"' target='_blank'>Download</a>";
                          }else{
                              html = "";
                          }*/
                     //   html='<div class="overlay"  onClick=window.open("lastcomment.php/?revid='.data.'","Ratting","width=550,height=170,0,status=0,scrollbars=1")>...</div>';
                      html=  '<a href="javascript:void(0);"NAME="Revision History Comments"  title=" Revision History Comments "' +
                          'onClick=window.open("lastcommentvendor.php/?revid='+row['id']+'","Ratting","width=550,height=170,0,status=0,scrollbars=1");>View Comments</a>';
                        return html;
                    },
                    "targets": [11]
                }

			],

			columns:[
				{ data: "index"},
				{ data: "client"},
				{ data: "chequeno"},
				{ data: "chequeDate"},
				{ data: "revDate"},
                { data: "UserID"},
				{ data: "amount"},
                { data: "reason"},
				{ data: "chequedepositfilepath"},
				{ data: "chequefilepath"}
			],
			"pageLength": 10
			/* <?php echo $_SESSION['DefaultDisplayRecordsMax']; ?>*/
		});

		$('#datatable_length')
			.find("label")
			.html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>ReversedCheques Vendors</h3>");


	};

	$(document).ready(function(){
		datatableInit();
		$("tbody tr td").html("Loading...");
		$.get("reversedChequesVendors.php?json", function(res, status){
			datatable.rows.add(JSON.parse(res)).draw(false);
		});
	});
    $('#datatable').on('change','.comment',function(){
        let revid = $(this).attr('data-revid');
        let value = $(this).val();
        $.post("api/addRevisionHistoryCommentsVendor.php",{revid,value},function(data, status){
            console.log("Data: " + data + "\nStatus: " + status);
        });
        $(this).val("");
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