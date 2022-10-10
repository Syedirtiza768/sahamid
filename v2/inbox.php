<?php 

	$active = "dashboard";

	include_once("config.php");



        $SQL = "SELECT * FROM inbox WHERE inbox.username= '".$_SESSION['UserID']."'ORDER BY id DESC";

        $res = mysqli_query($db, $SQL);

	include_once("includes/header.php");
	include_once("includes/sidebar.php");	
	
?>
<style>
	.table-head{position: relative; top:60px; background: #424242; left: 0; color: white;} .table-head th{border: 1px solid white;} .center-fit{width: 1%; white-space: nowrap; text-align: center;} .modal{display:none;position:fixed;z-index:1000;left:0;top: 0;width: 100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4);}.modal-content{background-color:#fefefe;margin: 5% auto;padding: 20px;border: 1px solid #888;width: 80%;}.close {color: #aaa;float: right;font-size: 28px;font-weight: bold;}.close:hover,.close:focus{color: black;text-decoration: none;cursor: pointer;}.tasktitle{border:1px solid #424242; border-radius: 7px; padding: 5px; width: 100%}.taskdescription{border:1px solid #424242; border-radius: 7px;padding: 5px; width: 100%; max-width: 100%; min-width: 100%;min-height: 100px;max-height: 100px;}#saveNewTask{margin-top:15px;}.warningsbox{display:none; margin-top: 15px;}.dontFBreak{width: 1%; white-space: nowrap;}td{vertical-align: middle !important;}.tooltip {position: relative;display: inline-block;border-bottom: 1px dotted black;visibility: visible !important;opacity: 1 !important;z-index: 998 !important;}.tooltip .tooltiptext {visibility: hidden;width: 400px;background-color: black;color: #fff;text-align: center;border-radius: 6px;padding: 10px;white-space: pre-wrap;position: absolute;top: -17px;left: 105%;}.tooltip:hover .tooltiptext {visibility: visible;background: #424242;}.dataTables_wrapper .dataTables_filter input{border:1px solid #424242; border-radius: 7px; padding:6px;} #datatb_wrapper{padding-top:5px;padding-bottom: 10px;}#datatb_info{padding-left:10px}
</style>
<link rel="stylesheet" href="assets/datatables/datatables.min.css" />
<link rel="stylesheet" href="assets/datatables/buttons.datatables.min.css" />
<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
<div class="content-wrapper">
    <section class="content-header">
      <h2>Inbox</h2>
    </section>

    <section class="content">
	    
		<div class="row">
			<div class="col-md-12">
				<div class="box box-primary">
		            <table class="table table-striped" id="datatb" style="margin-top:5px">
		            	<thead>
		            		<tr style="background-color: #424242; color: white;">

								<th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>

							</tr>
		            	</thead>
		            	<tbody>
		            	<?php while($row = mysqli_fetch_assoc($res)){ ?>
						<tr>
                            <td><?php ec($row['heading']); ?></td>
                            <td><?php ec($row['message']); ?></td>
                            <td><?php ec(date('d-M-y h:i A',strtotime($row["createdAt"]))); ?></td>
						</tr>
		            	<?php } ?>	
		            	</tbody>
		            </table>
	        	</div>
			</div>
		</div>

    </section>

</div>

<?php
	include_once("includes/footer.php");
?>
<script src="assets/datatables/jquery.dataTables.min.js"></script>
<script src="assets/datatables/dataTables.buttons.min.js"></script>
<script src="assets/datatables/buttons.html5.min.js"></script>
<script>
	$(document).ready(function(){
		let datatable = $('#datatb').DataTable({
					dom: 'Bfrtip',
					buttons: [

			        ],
					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Search..."
				    }
				});
	});
</script>
<?php
	include_once("includes/foot.php");
?>