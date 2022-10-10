<?php
	// Defining Prefix
	$PathPrefix='../';
	//Adding Required Libraries

	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');
	//SQL Queries  for applying filters

	$SQL = "SELECT categoryid, categorydescription FROM stockcategory";

	$result = mysqli_query($db, $SQL);
	
	$stockCategories = [];

	while($row = mysqli_fetch_assoc($result)){
		$stockCategories[$row['categoryid']] = $row['categorydescription'];
	}
// Main HTML
?>

<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="UTF-8">
	<title>SAHamid ERP - Item Search</title>

	<link rel="stylesheet" href="../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../quotation/assets/vendor/sweetalert/sweetalert.css" />
	<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
	<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
	<link rel="stylesheet" href="../quotation/assets/vendor/pnotify/pnotify.custom.css">
	<link rel="stylesheet" href="../quotation/assets/stylesheets/theme.css" />
	<link rel="stylesheet" href="../quotation/assets/stylesheets/skins/default.css" />

	<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>
	
	<style>
		th,td{
			text-align: center;
		}

		#searchresults_length label select {
			color: black;
		}

		#searchresults_length,#searchresults_info{
			color: black;
		}

		#searchresults_filter label,.datatables-footer,.datatables-header{
			width: 100%;
		}

		#searchresults thead th{
			border: 1px white solid;
			border-bottom: 0px;
		}

		#searchresults tfoot th{
			border: 1px white solid;
			border-top: 0px;
		}

		#searchresults td{
			border: 1px #424242 solid;
			width: 1%;
		}

		#scrollUp{
			position: fixed;
			bottom: 50px;
			right: 0;
			padding:10px;
			color: white;
			background: #424242;
		}

		.inp{
		    border: 1px solid #E5E7E9;
			border-radius: 6px;
			height: 46px;
			padding: 12px;
			outline: none;
		}

		.actinf{
			font-size: 10px;
		}

		.fit{
			width: 1%;
		}
	</style>

</head>
<body>

	<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
	  	<span style="color:white">
	  		<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> 
	  		&nbsp;|&nbsp;
	  		<span style="color:#ccc">
	  			<?php echo stripslashes($_SESSION['UsersRealName']); ?>
	      </span>
	  		<span class="pull-right" style="background:#424242; padding: 0 10px;">
	  			<a href="<?php echo $RootPath; ?>/../index.php" style="color: white; text-decoration: none;">Main Menu</a>
	  			<a class="bold" href="<?php echo $RootPath; ?>/../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
	  		</span>
	  	</span>
	</header>

	<div class="col-md-12" style=" background-color:#ecedf0; margin-bottom:50px;">

	    <h2 style="text-align:center; color:black">Search <span>I</span>tems</h2>
      	<div class="col-md-12" style="margin-bottom: -20px; text-align: center; color:black">
      		<div class="col-md-4">Category</div>
      		<div class="col-md-4">Description</div>
      		<div class="col-md-4">Stock Code</div>
      	</div>
      	<div class="col-md-12">
	      	<form class="form-inline" style="margin:20px; text-align:center; color: #aaa;">
	      		<div>
	      		
				<div class="col-md-4">
	        	<select style="width:100%; margin-bottom: 10px; color: #655E5D" id="scat" class="" name="scategory">
	        		<option value="All">All</option>
		        <?php 
		        	foreach ($stockCategories as $key => $value) {
		        ?>
	        		<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
		        <?php
		        	} 
		        ?>
	        	</select>
	        	</div>
	        	</div>
	        	
		        <div class="col-md-4">
		        <input style="width: 100%; margin-bottom: 10px; color: #655E5D" class="inp" type="text" id="desce">
		        </div>
		        
		        <div class="col-md-4">
		        	<input id="scode" class="inp" style="width: 100%; margin-bottom: 10px; color: #655E5D" type="text">
		        </div>
			</form>

	      	
		</div>
		<div class="col-md-5">
		    <input class="btn btn-warning" style="margin:0 auto; width:100%; margin-bottom: 10px;" type="submit" id="bcr" value="Clear Results">
		</div>
		<div class="col-md-2">
			<select id="inventory-quantity">
				<option value="all">All</option>
				<option value="zero">Zero</option>
				<option value="non-zero">Non Zero</option>
			</select>
		</div>
		<div class="col-md-5">
	      	<input class="btn btn-primary" style="margin:0 auto; width:100%; margin-bottom: 10px;" type="submit" id="bss" value="Search">
		</div>
		<div id="resultscontainer" class="" style=" background-color:#ecedf0">
			<table id="searchresults" width="100%" class="responsive">
				<thead>
					<tr style="background:#424242; color:white">
						<th class="fit">Item Code</th>
						<th class="fit">Model #</th>
						<th class="fit">Part #</th>
						<th class="fit">Manufacturer</th>
						<th>Description</th>
						<th class="fit">Action</th>
					</tr>
				</thead>
				<tbody id="srb" style="color: black">
					
				</tbody>
				<tfoot>
					<tr style="background:#424242; color:white">
						<th>Item Code</th>
						<th>Model #</th>
						<th>Part #</th>
						<th>Manufacturer</th>
						<th>Description</th>
						<th>Action</th>
					</tr>
				</tfoot>
			</table>
		</div>
    </div>

    <footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; z-index:150; padding:5px 0">
		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	</footer>
	// Scripts 
    <script src="../quotation/assets/vendor/jquery/jquery.js"></script>
	<script src="../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
	<script src="../quotation/assets/vendor/nprogress/nprogress.js"></script>
	<script src="../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
	<script src="../quotation/assets/vendor/pnotify/pnotify.custom.js"></script>
	<script src="../quotation/assets/javascripts/theme.js"></script>

	<script>
		var table = null;
		$(document).ready(function(){
			//datatable colummn initialization
			table = $('#searchresults').DataTable({
				"columns": [
		            { "data": "stockid" },
		            { "data": "mnfcode" },
		            { "data": "mnfpno" },
		            { "data": "mname" },
		            { "data": "description" },
		            { "data": "action" },
	        	]
			});
			//footer search script
			$('#searchresults tfoot th').each( function (i) {
		        var title = $('#searchresults thead th').eq( $(this).index() ).text(); 
		        if(title != "Action"){
		        	$(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" style="border:1px solid #424242; border-radius:7px; color:black; text-align:center" />' );  
		        } 
		    });

		    table.columns().every( function () {
		        var that = this;
		 
		        $('input', this.footer()).on('keyup change', function (){
		            if(that.search() !== this.value){
		                that.search(this.value).draw();
		            }
		        });
		    });

		});
		//Script for applying top filters
		$("#bss").click(function(e){
			e.preventDefault();

	    	NProgress.start();

			let code = $("#scode").val();
			let cat  = $("#scat").val();
			let desc  = $("#desce").val();
			let qty  = $("#inventory-quantity").val();
			let FormID = "<?php echo $_SESSION['FormID']; ?>";

			$.ajax({
				type: 'POST',
			    url: "api/itemSearch.php",
			    data: {StockCode: code, StockCat: cat, Description: desc, FormID: FormID,qty:qty},
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
	</script>
	
</body>
</html>