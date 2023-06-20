<?php 

	$active = "dashboard";

	include_once("config.php");
	
	if(isset($_GET['json'])){
	    $SQL = "SELECT ROUND(ABS(debtortrans.ovamount)) as amount,
				ROUND(ABS(debtortrans.alloc)) as alloc,
				ROUND(ABS(debtortrans.ovamount - debtortrans.alloc)) as remaining,
				chequefilepath as cheque,
				chequedepositfilepath as deposit,
				cashfilepath as cash,
				debtortrans.id,
				debtorsmaster.name,
				banktrans.ref as chequeno,
				debtortrans.invtext,
				debtortrans.reference,
				debtortrans.trandate
				FROM debtortrans
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN banktrans ON (debtortrans.transno=banktrans.transno AND banktrans.type=debtortrans.type)
				WHERE debtortrans.type = 12";
		$res = mysqli_query($db, $SQL);
		
		$response = [];
		
		$count = 1;
		while($row = mysqli_fetch_assoc($res)){

			$row['index'] = $count;
            $cheque= $row['cheque'];
            $deposit=$row['deposit'];
            $crv=$row['cash'];


           if (strlen($cheque)!= '')
                {
                    $row['cheque']="<a class='btn btn-info' href='".$cheque."' target='_blank'>Download</a>";
                }
            else
            {
                $row['cheque']="<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
            <input type='hidden' name='FormID' value=' " . $_SESSION['FormID'] . " ' />
            <input type='file' id='attachChequeFile" . $row['id'] . "' data-orderno='" . $row['id'] . "' class='attachChequeFile' name='cheque'>
            <input type='button' id='uploadChequeFile' data-orderno='" . $row['id'] . "' class='uploadChequeFile' name='uploadChequeFile' value='Upload Cheque'>
            </form>";
            }

            if ($row['deposit']!='')
            {
                $row['deposit']="<a class='btn btn-info' href='".$deposit."' target='_blank'>Download</a>";;
            }
            else
            {
                $row['deposit']="<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
            <input type='hidden' name='FormID' value=' " . $_SESSION['FormID'] . " ' />
            <input type='file' id='attachCdrFile" . $row['id'] . "' data-orderno='" . $row['id'] . "' class='attachCdrFile' name='cdr'>
            <input type='button' id='uploadCdrFile' data-orderno='" . $row['id'] . "' class='uploadCdrFile' name='uploadCdrFile' value='Upload CDR'>
            </form>";
            }
            if (strlen($crv)!= '')
            {
                $row['cash']="<a class='btn btn-info' href='".$crv."' target='_blank'>Download</a>";
            }
            else
            {
                $row['cash']="<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
            <input type='hidden' name='FormID' value=' " . $_SESSION['FormID'] . " ' />
            <input type='file' id='attachCRVFile" . $row['id'] . "' data-orderno='" . $row['id'] . "' class='attachCRVFile' name='CRV'>
            <input type='button' id='uploadCRVFile' data-orderno='" . $row['id'] . "' class='uploadCRVFile' name='uploadCRVFile' value='Upload CRV'>
            </form>";
            }
            //$row['deposit']=$deposit;

			$row['trandate'] = date("Y/m/d",strtotime($row['trandate']));
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
							<th class="fit">Date</th>
                            <th class="fit">Ref1</th>
                            <th class="fit">Ref2</th>
                            <th class="fit">Ref3</th>
							<th class="fit">Amount</th>
							<th class="fit">Alloc</th>
							<th class="fit">Remaining</th>
							<th class="fit">CDR</th>
							<th class="fit no-wrap">Cheque</th>
                            <th class="fit">CRV</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
					<tfoot>
						<tr style="background-color: #424242; color: white">
							<th class="fit">SNo</th>
							<th class="fit">Client</th>
							<th class="fit">Date</th>
                            <th class="fit">Ref1</th>
                            <th class="fit">Ref2</th>
                            <th class="fit">Ref3</th>
							<th class="fit">Amount</th>
							<th class="fit">Alloc</th>
							<th class="fit">Remaining</th>
							<th class="fit">CDR</th>
                           <th class="fit no-wrap">Cheque</th>
                            <th class="fit">CRV</th>
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
						return data;
					},
					"targets": [ 0 ] 
				},
				{ 
					className: "no-wrap", 
					"render": function ( data, type, row ) {
						return data;
					},
					"targets": [ 1 ] 
				},
				{ 
					className: "fit center no-wrap", 
					"render": function ( data, type, row ) {
						return data;
					},
					"targets": [ 2 ] 
				},
				{ 
					className: "fit center no-wrap", 
					"render": function ( data, type, row ) {
						return ((data < 0) ? (-1*data):(data))+"<sub> PKR</sub>";
					},
					"targets": [ 6 ]
				}

			],
			columns:[
				{ data: "index"},
				{ data: "name"},
				{ data: "trandate"},
                { data: "chequeno"},
                { data: "invtext"},
                { data: "reference"},
				{ data: "amount"},
				{ data: "alloc"},
				{ data: "remaining"},
				{ data: "deposit"},
				{ data: "cheque"},
                { data: "cash"}
			],
			"pageLength": 10

		});

		$('#datatable_length')
			.find("label")
			.html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Uploaded Cheques</h3>");


	};

	$(document).ready(function(){
		datatableInit();
		$("tbody tr td").html("Loading...");
		$.get("uploadedCheques.php?json", function(res, status){
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
    $('#datatable').on('click','.uploadChequeFile',function(){
        var ref=$(this);
        var orderno = $(this).attr('data-orderno');
        var fd = new FormData();


        var files = $('#attachChequeFile'+orderno)[0].files[0];

        fd.append('cheque', files);
        fd.append('orderno',orderno);

        $.ajax({
            url: 'api/uploadChequeFile.php',
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            success: function(res){
                if(res){
                    ref.parent().html(res);
                }
                else{
                    alert('file not uploaded');
                }
            },
        });
    });

    $('#datatable').on('click','.uploadCdrFile',function(){
        var ref=$(this);
        var orderno = $(this).attr('data-orderno');
        var fd = new FormData();


        var files = $('#attachCdrFile'+orderno)[0].files[0];

        fd.append('cdr', files);
        fd.append('orderno',orderno);

        $.ajax({
            url: 'api/uploadCdrFile.php',
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            success: function(res){
                if(res){
                    ref.parent().html(res);
                }
                else{
                    alert('file not uploaded');
                }
            },
        });
    });

    $('#datatable').on('click','.uploadCRVFile',function(){
        var ref=$(this);
        var orderno = $(this).attr('data-orderno');
        var fd = new FormData();


        var files = $('#attachCRVFile'+orderno)[0].files[0];

        fd.append('CRV', files);
        fd.append('orderno',orderno);

        $.ajax({
            url: 'api/uploadCRVFile.php',
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            success: function(res){
                if(res){
                    ref.parent().html(res);
                }
                else{
                    alert('file not uploaded');
                }
            },
        });
    });


</script>
<?php
	include_once("includes/foot.php");
?>